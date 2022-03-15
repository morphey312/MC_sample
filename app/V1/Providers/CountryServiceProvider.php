<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class CountryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/countries.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\CountryRepository',
            'App\V1\Repositories\CountryRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CountryFilter',
            'App\V1\Repositories\Query\CountryFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CountrySorter',
            'App\V1\Repositories\Query\CountrySorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\CountryScopes',
            'App\V1\Repositories\Query\CountryScopes'
        );
    }
}
