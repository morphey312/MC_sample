<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\DiscountCardType;

class DiscountCardTypeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/discount-card-type.php'));

        DiscountCardType::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DiscountCardTypeRepository',
            'App\V1\Repositories\DiscountCardTypeRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardTypeFilter',
            'App\V1\Repositories\Query\DiscountCardTypeFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardTypeSorter',
            'App\V1\Repositories\Query\DiscountCardTypeSorter'
        );
    }
}
