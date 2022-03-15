<?php


namespace App\V1\Services\Esputnik;

use App\V1\Contracts\Services\Esputnik\Sender as Contract;
use App\V1\Models\Notification\MailingProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Illuminate\Support\Facades\Log;

class Sender implements Contract
{
    /**
     * @var Client
     */
    protected $client;
    protected $timeout;

    /**
     * @param MailingProvider $provider
     */
    public function __construct(MailingProvider $provider)
    {
        $config = [
            'base_url' =>  $provider->settings ? $provider->settings['base_url'] : '',
            'timeout' => $provider->settings ? $provider->settings['timeout'] : '',
            'defaults' => [
                'auth' => [
                    $provider->account_name ?? '',
                    $provider->account_password ?? '',
                ]
            ]
        ];

        $this->timeout = $provider->settings ? $provider->settings['timeout'] : 15;
        $this->client = new Client($config);
    }

    /**
     * @param $method
     * @param $endpoint
     * @param $data
     * @return bool
     */
    public function request($method, $endpoint, $data): bool
    {
        if (config('esputnik.debug')) {
            Log::channel('stderr')->info('EsputnikSendedData', [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data
            ]);

            return false;
        }

        $request = $this->client->createRequest($method, $endpoint, ['json' => $data]);

        try {
            /**
             * @var Response $response
             */
            $response = retry(
                3,
                function () use ($request) {
                    return $this->client->send($request);
                },
                (int)$this->timeout/ 3
            );

            Log::channel('stderr')->info('EsputnikSendedData', [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data
            ]);

            if ($response->getStatusCode() === 200) {
                $responseBody = $response->json();
                Log::channel('stderr')->info('$responseBody',[$responseBody]);

                if (!isset($responseBody['failedContacts'])) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $exception) {
            Log::channel('stderr')
                ->error("{$exception->getCode()}: Esputnik request failed.", [
                    'error' => $exception->getMessage(),
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'trace' => $exception->getTrace()
                ]);
        }

        return false;
    }
}
