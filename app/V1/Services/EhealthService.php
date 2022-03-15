<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\EhealthService as EhealthServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Exception;
use App\V1\Models\Ehealth\User;
use App\V1\Models\Msp;
use App\V1\Models\Employee;
use App\V1\Models\FileAttachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EhealthService implements EhealthServiceInterface
{
    const USER_AGENT = 'Medcenterplus Agent';

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = config('services.ehealth');
        $this->endpoint = $config['api_endpoint'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_url'];
        if (!$this->redirectUri) {
            $this->redirectUri = config('app.url');
        }
    }

    /**
     * @inheritdoc
     */
    public function makeFingerprint($clientId, $userId)
    {
        return base64_encode(json_encode([
            'id' => $userId,
            'client' => $clientId,
            'hash' => $this->makeFingerprintHash($clientId, $userId),
        ]));
    }

    /**
     * @inheritdoc
     */
    public function checkFingerprint($fingerprint)
    {
        $data = json_decode(base64_decode($fingerprint), true);

        if ($data && !empty($data['id']) && !empty($data['client']) && !empty($data['hash'])) {
            $userId = $data['id'];
            $clientId = $data['client'];
            $hash = $this->makeFingerprintHash($clientId, $userId);
            if ($hash === $data['hash']) {
                return [$clientId, $userId];
            }
        }

        return [null, null];
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUri($clientId, $userId)
    {
        return $this->redirectUri . '?FP=' . $this->makeFingerprint($clientId, $userId);
    }

    /**
     * Make hash for fingerprint
     *
     * @param int $clientId
     * @param int $userId
     *
     * @return string
     */
    protected function makeFingerprintHash($clientId, $userId)
    {
        return md5($userId . $clientId . $this->clientSecret);
    }

    /**
     * @inheritdoc
     */
    public function authorize($code, $localUserId = null, $clientId = null)
    {
        if (!empty($clientId)) {
            $clientSecret = $this->getClientSecret($clientId);
            $redirectUri = $this->getRedirectUri($clientId, $localUserId);
        } else {
            $clientId = $this->clientId;
            $clientSecret = $this->clientSecret;
            $redirectUri = $this->redirectUri;
        }

        $response = $this->post('oauth/tokens', [
            'token' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ],
        ]);

        $token = $response['data']['value'];
        $userId = $response['data']['user_id'];
        $expires = $response['data']['expires_at'];
        $refreshToken = $response['data']['details']['refresh_token'];

        $user = User::where('ehealth_id', '=', $userId)->first();

        if ($user === null && $localUserId !== null) {
            $user = new User();
            $user->ehealth_id = $userId;
            $user->user_id = $localUserId;
            $user->client_id = $clientId;
            $this->bindEmployee($user);
        }

        if ($user !== null) {
            $user->access_token = $token;
            $user->refresh_token = $refreshToken;
            $user->token_expires = Carbon::createFromTimestamp($expires);
            $user->save();
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getToken(User $user)
    {
        if ($user->access_token !== null) {
            if ($user->token_expires->isFuture()) {
                return $user->access_token;
            }
        }

        if ($user->refresh_token !== null) {
            return $this->refreshToken($user);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function refreshToken(User $user)
    {
        if ($user->client_id === $this->clientId) {
            $clientSecret = $this->clientSecret;
        } else {
            $clientSecret = $this->getClientSecret($user->client_id);
        }

        $response = $this->post('oauth/tokens', [
            'token' => [
                'client_id' => $user->client_id,
                'client_secret' => $clientSecret,
                'refresh_token' => $user->refresh_token,
                'grant_type' => 'refresh_token',
            ],
        ]);

        $token = $response['data']['value'];
        $expires = $response['data']['expires_at'];
        $user->access_token = $token;
        $user->token_expires = Carbon::createFromTimestamp($expires);
        $user->save();

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function getMsp($legalEntityId)
    {
        return Msp::where('ehealth_id', '=', $legalEntityId)->first();
    }

    /**
     * @inheritdoc
     */
    public function submitMsp($signedData, $authToken)
    {
        return $this->put('api/v2/legal_entities', [
            'signed_legal_entity_request' => $signedData,
            'signed_content_encoding' => 'base64',
        ], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getLegalEntity($ehealthId)
    {
        $mis = $this->getMisUser();

        if ($mis === null) {
            return [];
        }

        try {
            $authToken = $this->getToken($mis);
            return $this->get('api/v2/legal_entities/' . $ehealthId, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function disableMsp($ehealthId, $authToken)
    {
        return $this->patch('api/legal_entities/' . $ehealthId . '/actions/deactivate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function submitClinic($data, $authToken, $ehealthId = null)
    {
        if ($ehealthId !== null) {
            return $this->patch('api/divisions/' . $ehealthId, $data, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        } else {
            return $this->post('api/divisions', $data, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function disableClinic($ehealthId, $authToken)
    {
        return $this->patch('api/divisions/' . $ehealthId . '/actions/deactivate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function enableClinic($ehealthId, $authToken)
    {
        return $this->patch('api/divisions/' . $ehealthId . '/actions/activate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function submitEmployee($signedData, $authToken)
    {
        return $this->post('api/v2/employee_requests', [
            'signed_content' => $signedData,
            'signed_content_encoding' => 'base64',
        ], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function disableEmployee($ehealthId, $authToken)
    {
        return $this->patch('api/employees/' . $ehealthId . '/actions/deactivate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function submitEmployeeServiceType($data, $authToken)
    {
        return $this->post('api/employee_roles', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function disableEmployeeServiceType($ehealthId, $authToken)
    {
        return $this->patch('api/employee_roles/' . $ehealthId . '/actions/deactivate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function initContract($type, $authToken)
    {
        return $this->post('api/contract_requests/' . $type, [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function submitContract($id, $type, $signedData, $authToken)
    {
        return $this->post('api/contract_requests/' . $type . '/' . $id, [
            'signed_content' => $signedData,
            'signed_content_encoding' => 'base64',
        ], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function approveContract($id, $type, $authToken)
    {
        return $this->patch('api/contract_requests/' . $type . '/' . $id . '/actions/approve_msp', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function signContract($id, $type, $signedData, $authToken)
    {
        return $this->patch('api/contract_requests/' . $type . '/' . $id . '/actions/sign_msp', [
            'signed_content' => $signedData,
            'signed_content_encoding' => 'base64',
        ], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function terminateContract($id, $type, $authToken)
    {
        return $this->patch('api/contract_requests/' . $type . '/' . $id . '/actions/terminate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function submitServiceType($data, $authToken, $ehealthId = null)
    {
        if ($ehealthId !== null) {
            return $this->patch('api/healthcare_services/' . $ehealthId, $data, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        } else {
            return $this->post('api/healthcare_services', $data, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function disableServiceType($ehealthId, $authToken)
    {
        return $this->patch('api/healthcare_services/' . $ehealthId . '/actions/deactivate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function enableServiceType($ehealthId, $authToken)
    {
        return $this->patch('api/healthcare_services/' . $ehealthId . '/actions/activate', [], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getEmployeeRequest($requestId)
    {
        $mis = $this->getMisUser();

        if ($mis === null) {
            return null;
        }

        try {
            $token = $this->getToken($mis);
            return $this->get('api/mis/employee_requests/' . $requestId, [], [
                'Authorization' => 'Bearer ' . $token,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getContractRequest($type, $requestId, $authToken)
    {
        return $this->get('api/contract_requests/' . $type . '/' . $requestId, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getContract($type, $id, $authToken)
    {
        return $this->get('api/contracts/' . $type . '/' . $id, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function searchLegalEntities($params)
    {
        $mis = $this->getMisUser();

        if ($mis === null) {
            return null;
        }

        try {
            $token = $this->getToken($mis);
            return $this->get('api/v2/legal_entities', $params, [
                'Authorization' => 'Bearer ' . $token,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function searchMedicalPrograms($params)
    {
        $mis = $this->getMisUser();

        if ($mis === null) {
            return null;
        }

        try {
            $token = $this->getToken($mis);
            return $this->get('api/medical_programs', $params, [
                'Authorization' => 'Bearer ' . $token,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function searchPatients($params, $token)
    {
        try {
            return $this->get('api/persons', $params, [
                'Authorization' => 'Bearer ' . $token,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function uploadDocument($url, FileAttachment $file)
    {
        $content = Storage::disk($file->disk)->get($file->path);

        $this->put($url, $content);
    }

    /**
     * @inheritdoc
     */
    public function downloadDocument($url)
    {
        try {
            $options = $this->setupRequestOptions([], [], []);
            $client = $this->getHttpClient();
            $request = $client->createRequest('GET', $url, $options);
            $response = $client->send($request);
            $code = $response->getStatusCode();
            if (!in_array($code, [200, 201])) {
                $this->logError('GET', $url, $code, [], [], []);
                throw new Ehealth\UnprocessibleRequestException([], $code);
            }
            $stream = $response->getBody();
            $body = '';
            while (!$stream->eof()) {
                $body .= $stream->read(4096);
            }
            return $body;
        } catch (RequestException $e) {
            $this->logError('GET', $url, $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDictionaries()
    {
        return $this->get('api/dictionaries');
    }

    /**
     * @inheritdoc
     */
    public function getServices()
    {
        return $this->get('api/services');
    }

    /**
     * Get http client
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'base_url' => $this->endpoint,
                'defaults' => [
                    'timeout' => 60,
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Send post request to eHealth
     *
     * @param string $path
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function post($path, $data = [], $query = [], $headers = [])
    {
        return $this->doRequest('POST', $path, $data, $query, $headers);
    }

    /**
     * Send put request to eHealth
     *
     * @param string $path
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function put($path, $data = [], $query = [], $headers = [])
    {
        return $this->doRequest('PUT', $path, $data, $query, $headers);
    }

    /**
     * Send patch request to eHealth
     *
     * @param string $path
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function patch($path, $data = [], $query = [], $headers = [])
    {
        return $this->doRequest('PATCH', $path, $data, $query, $headers);
    }

    /**
     * Send get request to eHealth
     *
     * @param string $path
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function get($path, $query = [], $headers = [])
    {
        return $this->doRequest('GET', $path, [], $query, $headers);
    }

    /**
     * Send a request to eHealth
     *
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $headers
     *
     * @return array
     */
    protected function doRequest($method, $path, $data = [], $query = [], $headers = [])
    {
        try {
            $options = $this->setupRequestOptions($data, $query, $headers);
            $client = $this->getHttpClient();
            $request = $client->createRequest($method, $path, $options);
            $response = $client->send($request);
            $code = $response->getStatusCode();
            $body = (string) $response->getBody();
            $json = $body ? json_decode($body, true) : [];
            \Log::info($json);
            \Log::info('---------------- $json');
            \Log::info($request);
            \Log::info('---------------- $request');
            \Log::info($code);
            \Log::info('---------------- $code -----------------------------------------------');

            if (!in_array($code, [200, 201])) {
                $this->logError($method, $path, $code, $data, $query, $json);
                throw new Ehealth\UnprocessibleRequestException($json, $code);
            }
            return $json;
        } catch (RequestException $e) {
            $this->logError($method, $path, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Setup request options
     *
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function setupRequestOptions($data, $query, $headers)
    {
        $options = [
            'exceptions' => false,
        ];

        $options['headers'] = array_merge([
            'User-Agent' => static::USER_AGENT,
            'X-Requested-With' => 'XMLHttpRequest',
        ], $headers);

        if ($data !== []) {
            if (is_string($data)) {
                $options['body'] = $data;
            } else {
                $options['json'] = $data;
            }
        }

        if ($query !== []) {
            $options['query'] = $query;
        }

        return $options;
    }

    /**
     * Log error message
     *
     * @param string $method
     * @param string $path
     * @param mixed $status
     * @param array $input
     * @param array $output
     */
    protected function logError($method, $path, $status, $data = null, $query = null, $response = null)
    {
        Log::error('Request error: ' . $method. ' ' . $path . ': ' . $status, [
            'data' => $data,
            'query' => $query,
            'response' => $response,
        ]);
    }

    /**
     * Get client secret by ID
     *
     * @param string $clientId
     *
     * @return string
     */
    protected function getClientSecret($clientId)
    {
        if ($clientId === $this->clientId) {
            return $this->clientSecret;
        }

        return Msp::where('client_id', '=', $clientId)->value('client_secret');
    }

    /**
     * @inherit
     */
    public function getMisUser()
    {
        return User::where('client_id', '=', $this->clientId)->first();
    }

    /**
     * @inherit
     */
    public function getMisClientId()
    {
        return $this->clientId;
    }

    /**
     * Bind MC employee with eHealth employee
     *
     * @param User $user
     */
    protected function bindEmployee($user)
    {
        $employee = $user->extract('mc_user.userable');

        if ($employee instanceof Employee) {
            if ($employee->ehealth_request_id !== null) {
                $employee_id = $this->getEmployeeIdByRequestId($employee->ehealth_request_id);
                if ($employee_id !== null) {
                    $employee->ehealth_id = $employee_id;
                    $employee->save();
                }
            }
        }
    }

    /**
     * Get ehealth employee ID by employee request ID
     *
     * @param string $requestId
     *
     * @return string
     */
    protected function getEmployeeIdByRequestId($requestId)
    {
        $response = $this->getEmployeeRequest($requestId);

        if ($response === null) {
            return null;
        }

        return $response['data']['employee_id'];
    }

    /**
     * @inheritdoc
     */
    public function submitPatient($data, $authToken)
    {
        return $this->post('api/person_requests', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function approvePatient($id, $data, $authToken)
    {
        return $this->patch('api/person_requests/' . $id . '/actions/approve', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function signPatient($id, $signedData, $authToken)
    {
        return $this->patch('api/person_requests/' . $id . '/actions/sign', [
            'signed_content' => $signedData,
        ], [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPersonRequestById($id, $authToken)
    {
        return $this->get('api/person_requests/' . $id, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function resendOtp($id, $authToken)
    {
        try {
            return $this->post('api/person_requests/' . $id . '/actions/resend_otp', [], [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function getPatientAuthentications($id, $token)
    {
        return $this->get('api/persons/' . $id . '/authentication_methods', [], [
            'Authorization' => 'Bearer ' . $token,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findVerificationsByPhoneNymber($phoneNumber, $authToken)
    {
        try {
            return $this->get('api/verifications/' . $phoneNumber, [], [
                'Authorization' => 'Bearer ' . $authToken,
                'API-key' => $this->clientSecret,
            ]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function submitAuthentication($patientId, $data, $authToken)
    {
        return $this->post('api/persons/' . $patientId . '/authentication_method_requests', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function approveAuthentication($patientId, $requestId, $data, $authToken)
    {
        return $this->patch('api/persons/' . $patientId . '/authentication_method_requests/' . $requestId . '/actions/approve', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function initializeOtp($data, $authToken)
    {
        return $this->post('api/verifications', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function completeOtp($phone, $data, $authToken)
    {
        return $this->patch('api/verifications/' . $phone . '/actions/complete', $data, [], [
            'Authorization' => 'Bearer ' . $authToken,
            'API-key' => $this->clientSecret,
        ]);
    }
}
