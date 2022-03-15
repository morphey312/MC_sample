<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\LaboratoryClientService as LaboratoryClientServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LaboratoryClientService implements LaboratoryClientServiceInterface
{
     /**
     * \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $api_token;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {   
        $config = config('services.laboratory');
        $this->endpoint = $config['endpoint'];
        $this->api_token = $config['api_token'];
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
    public function getList($list, $query = [], $headers = [])
    {   
        $result = $this->doRequest('GET', $list, [], $query, $headers);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function sendTransfer($data = [])
    {
        $result = $this->doRequest('POST', 'transfer-sheets', $data);
        return $result;
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
            $code = $response->getStatusCode();
            $body = (string) $response->getBody();
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
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => sprintf('Bearer %s', $this->api_token),
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
}
