<?php

namespace App\V1\Providers\DiscountCardType;

use Illuminate\Support\ServiceProvider;

class IconServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/discount-card-type/icon.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DiscountCardType\IconRepository',
            'App\V1\Repositories\DiscountCardType\IconRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardType\IconFilter',
            'App\V1\Repositories\Query\DiscountCardType\IconFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DiscountCardType\IconSorter',
            'App\V1\Repositories\Query\DiscountCardType\IconSorter'
        );
    }
}
