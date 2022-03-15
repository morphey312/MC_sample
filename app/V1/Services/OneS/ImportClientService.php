<?php

namespace App\V1\Services\OneS;

use App\V1\Contracts\Services\OneS\ImportClientService as ImportClientServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\V1\Traits\Services\OneS\ResponseProcess;

class ImportClientService implements ImportClientServiceInterface
{
    use ResponseProcess;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(config('services.one_s.import'));
    }

    /**
     * Send specified import request
     *
     * @param string $command
     *
     * @return array
     */
    public function sendImportCommand($command)
    {
        $request = $this->createCommandRequest($command);

        try {
            $response = $this->client->send($request);
            $code = (int)$response->getStatusCode();

            if ($this->codeSuccess === $code) {
                return ['data' => $this->getDecodedBody($response)];
            }
            return ['error' =>  $this->responseMessage($command, $response)];
        } catch (RequestException $e) {
            return ['error' => $this->responseMessage($command, $e->getResponse())];
        }
    }

    /**
     * Create command request
     *
     * @param string $command
     *
     * @return HTTP request object
     */
    protected function createCommandRequest($command)
    {
    	return $this->createRequest([
            'json' => [
            	'Command' => $command,
            ],
        ]);
    }

    /**
     * Create HTTP request
     *
     * @param array  $options
     * @param string $method
     * @param string $url
     *
     * @return HTTP request object
     */
    protected function createRequest($options = [], $method = 'POST', $url = '')
    {
        return $this->client->createRequest($method, $url, $options);
    }

    /**
     * Get response error message
     *
     * @param Http $response
     *
     * @return string
     */
    protected function responseMessage($command, $response)
    {
    	return $command . " failed. " . $response->getEffectiveUrl() . " " . $response->getStatusCode() . "/" . $response->getReasonPhrase();
    }
}
