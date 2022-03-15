<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class ExchangeRateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/exchange-rate.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\ExchangeRateRepository',
            'App\V1\Repositories\ExchangeRateRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ExchangeRateFilter',
            'App\V1\Repositories\Query\ExchangeRateFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ExchangeRateSorter',
            'App\V1\Repositories\Query\ExchangeRateSorter'
        );
    }
}