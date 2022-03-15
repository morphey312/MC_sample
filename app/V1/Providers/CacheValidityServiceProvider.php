<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class CacheValidityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/cache-validity.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CacheValidityRepository',
            'App\V1\Repositories\CacheValidityRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CacheValidityFilter',
            'App\V1\Repositories\Query\CacheValidityFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CacheValiditySorter',
            'App\V1\Repositories\Query\CacheValiditySorter'
        );
    }
}
