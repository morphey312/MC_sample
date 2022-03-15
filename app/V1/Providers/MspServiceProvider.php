<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class MspServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/msp.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\MspRepository',
            'App\V1\Repositories\MspRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MspFilter',
            'App\V1\Repositories\Query\MspFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\MspSorter',
            'App\V1\Repositories\Query\MspSorter'
        );
    }
}
