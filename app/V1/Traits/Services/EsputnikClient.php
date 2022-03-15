<?php

namespace App\V1\Traits\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;


trait EsputnikClient
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $debugMode;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Setup basic client options
     *
     * @param array $config
     */
    public function setupClient($config)
    {
        $this->apiKey = $config['api_key'];
        $this->debugMode = $config['debug_mode'];
        $this->client = new Client([
            'base_url' => $config['api_endpoint'],
            'timeout' => $config['api_timeout'],
        ]);
    }

    /**
     * Send request to API gateway
     *
     * @param string $method
     * @param string $url
     * @param array $data
     *
     * @return array
     */
    protected function request($method, $url, $data = null)
    {
        $options = [
            'auth' => [
                'mc',
                $this->apiKey,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        if ($data !== null) {
            if (is_array($data)) {
                if ($method === self::GET) {
                    $options['query'] = $data;
                } else {
                    $options['json'] = $data;
                }
            } else {
                $options['body'] = $data;
            }
        }

        try {
            $response = $this->client->$method($url, $options);
            $body = (string) $response->getBody();
            return $body ? json_decode($body, true) : [];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $status = $e->getResponse()->getStatusCode();
                $output = (string) $e->getResponse()->getBody();
            } else {
                $status = 0;
                $output = null;
            }
            Log::error('Esputnik request error: ' . $method. ' ' . $url . ': ' . $status, [
                'request' => $data,
                'response' => $output,
            ]);
            return null;
        }
    }
}
