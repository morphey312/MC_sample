<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;

class RegistrationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/registration.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\RegistrationRepository',
            'App\V1\Repositories\Patient\RegistrationRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\RegistrationFilter',
            'App\V1\Repositories\Query\Patient\RegistrationFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\RegistrationSorter',
            'App\V1\Repositories\Query\Patient\RegistrationSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\RegistrationScopes',
            'App\V1\Repositories\Query\Patient\RegistrationScopes'
        );
    }
}