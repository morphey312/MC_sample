<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Models\ExchangeRate;
use App\V1\Models\Clinic;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Exception;

class GetExchangeRates extends Command
{
    const SKIP_CURRENCIES = ['uah'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:get-rates {--date_from=} {--date_to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get currency exchange rates for specific period by currency code - eur, rub etc.';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->httpClient = new Client([
            'base_url' => config('services.exchange.base_url'),
            'defaults' => [
                'timeout' => 30,
            ],
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $codes = Clinic::whereNotIn('currency', static::SKIP_CURRENCIES)
            ->distinct()->pluck('currency')
            ->toArray();

        if (empty($codes)) {
            return;
        }

        $dateFrom = $this->option('date_from') ?? Carbon::today();
        $dateTo = $this->option('date_to') ?? Carbon::today();
        $dateFrom = Carbon::parse($dateFrom);
        $options = [
            'query' => [
                'json' => true,
            ],
        ];

        while ($dateFrom->lessThanOrEqualTo($dateTo)) {
            try {
                $options['query']['date'] = $dateFrom->format('Ymd');
                foreach ($codes as $code) {
                    $options['query']['valcode'] = $code;
                    $request = $this->getData($options);
                    $response = $this->httpClient->send($request);
                    if ($response->getStatusCode() === 200) {
                        $result = $response->json();
                        $rate = $result[0];
                        ExchangeRate::firstOrCreate([
                            'date' => Carbon::parse($rate['exchangedate'])->format('Y-m-d'),
                            'code' => strtolower($rate['cc']),
                        ], [
                            'value' => $rate['rate'],
                        ]);
                    }
                }
                
            } catch (Exception $e) {
                $this->info($e->getMessage());
            }
            $dateFrom->addDay();
        }
    }

    /**
     * Make http request
     * 
     * @return http request
     */
    protected function getData($options = [], $method = 'GET')
    {
        return $this->httpClient->createRequest($method, null, $options);
    }
}