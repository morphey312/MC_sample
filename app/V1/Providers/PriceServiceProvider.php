<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Price;
use App\V1\Observers\Audit\PriceAudit;
use App\V1\Observers\PriceObserver;

class PriceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/price.php'));

        Price::observe(PriceObserver::class);
        Price::observe(PriceAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\PriceRepository',
            'App\V1\Repositories\PriceRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceFilter',
            'App\V1\Repositories\Query\PriceFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PriceSorter',
            'App\V1\Repositories\Query\PriceSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Price\SetRepository',
            'App\V1\Repositories\Price\SetRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Price\SetFilter',
            'App\V1\Repositories\Query\Price\SetFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Price\SetSorter',
            'App\V1\Repositories\Query\Price\SetSorter'
        );
    }
}
