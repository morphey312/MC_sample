<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class PriceAgreementActServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/price-agreement-act.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PriceAgreementActRepository',
            'App\V1\Repositories\PriceAgreementActRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceAgreementActFilter',
            'App\V1\Repositories\Query\PriceAgreementActFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceAgreementActSorter',
            'App\V1\Repositories\Query\PriceAgreementActSorter'
        );
    }
}
