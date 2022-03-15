<?php

namespace App\V1\Providers\Patient;

use Illuminate\Support\ServiceProvider;

class AssignedServiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/assigned-service.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\AssignedServiceRepository',
            'App\V1\Repositories\Patient\AssignedServiceRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedServiceFilter',
            'App\V1\Repositories\Query\Patient\AssignedServiceFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedServiceSorter',
            'App\V1\Repositories\Query\Patient\AssignedServiceSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\AssignedServiceScopes',
            'App\V1\Repositories\Query\Patient\AssignedServiceScopes'
        );
    }
}