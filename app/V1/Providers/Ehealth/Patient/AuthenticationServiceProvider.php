<?php

namespace App\V1\Providers\Ehealth\Patient;

use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/ehealth/patient/authentication.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Ehealth\Patient\AuthenticationRepository',
            'App\V1\Repositories\Ehealth\Patient\AuthenticationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\AuthenticationFilter',
            'App\V1\Repositories\Query\Ehealth\Patient\AuthenticationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Ehealth\Patient\AuthenticationSorter',
            'App\V1\Repositories\Query\Ehealth\Patient\AuthenticationSorter'
        );
    }
}
