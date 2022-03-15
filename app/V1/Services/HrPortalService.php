<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\HrPortalService as HrPortalServiceInterface;
use App\V1\Models\Employee;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Exception;

class HrPortalService implements HrPortalServiceInterface
{
    const USER_AGENT = 'MC+';

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $baseLoginUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = config('services.hr_portal');
        $this->endpoint = $config['endpoint'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->baseLoginUrl = $config['base_login_url'];
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
                    'timeout' => 30,
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * @inheritdoc
     */
    public function register(Employee $employee)
    {
        $clinic = $employee->employee_clinics()->first();
        $specializations = $clinic->specializations()->get(['specialization_id'])->toArray();
        $data = [
            'id' => $employee->id,
            'gender' => $employee->gender,
            'birth_date' => $employee->birth_date,
            'phone' => $employee->phone,
            'additional_phone' => $employee->additional_phone,
            'email' => $employee->email,
            'last_name' => $employee->last_name,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'main_clinic' => $clinic ? $clinic->clinic_id : null,
            'username' => $employee->user->login,
            'password' => $employee->user->password_recovery,
            'position' => $clinic->position_id ?? null,
            'status' => $clinic->status,
            'specializations' => ($specializations) ? array_column($specializations, 'specialization_id') : null,
        ];

        $result = $this->doRequest('POST', 'user/create-from-mc', $data);

        return $result !== false;
    }

    /**
     * @inheritdoc
     */
    public function getOnceToken($employeeId, $asLink = true)
    {
        $result = $this->doRequest('POST', 'user/get-once-token', ['id' => $employeeId]);

        if ($result === false || $result['token'] === false) {
            return false;
        }

        if ($asLink) {
            return $this->baseLoginUrl . 'auth/onceTokenLogin?' . http_build_query([
                    'once_token' => $result['token'],
                ]);
        } else {
            return $result['token'];
        }
    }

    /**
     * Send a request to portal
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
            $this->logError($method, $path, 400, null, null, $request->getUrl());
            $code = $response->getStatusCode();
            $body = (string)$response->getBody();
            $json = $body ? json_decode($body, true) : [];
            if (!in_array($code, [200, 201])) {
                $this->logError($method, $path, $code, $data, $query, $json);
                return false;
            }
            return $json;
        } catch (RequestException $e) {
            $this->logError($method, $path, $e->getMessage());
            return false;
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
            'Authorization' => sprintf('Basic %s', base64_encode(implode(':', [
                $this->username,
                $this->password,
            ]))),
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
        Log::error('Request error: ' . $method . ' ' . $path . ': ' . $status, [
            'data' => $data,
            'query' => $query,
            'response' => $response,
        ]);
    }

    /**
     * Change employee status on portal
     * @param int $id
     * @param string $status
     * @return mixed|void
     */
    public function updateEmployeeStatus(int $id, string $status)
    {
        $this->doRequest('POST', 'user/update-status-from-mc', [
            'id' => $id,
            'status' => $status
        ]);
    }

    /**
     * Create new position in portal
     * @param array $data
     * @return mixed
     */
    public function createNewPosition(array $data)
    {
        $this->doRequest('POST', 'create-new-position', $data);
    }


    /**
     * Create new clinic in portal
     * @param array $data
     * @return mixed|void
     */
    public function createNewClinic(array $data)
    {
        $this->doRequest('POST', 'create-new-clinic', $data);
    }
}
