<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;

class ClinicRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/clinic-routes.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\ClinicRouteRepository',
            'App\V1\Repositories\Patient\ClinicRouteRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\ClinicRouteFilter',
            'App\V1\Repositories\Query\Patient\ClinicRouteFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\ClinicRouteSorter',
            'App\V1\Repositories\Query\Patient\ClinicRouteSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\ClinicRouteScopes',
            'App\V1\Repositories\Query\Patient\ClinicRouteScopes'
        );
    }
}