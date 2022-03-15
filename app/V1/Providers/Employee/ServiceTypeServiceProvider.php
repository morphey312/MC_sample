<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class ServiceTypeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/service-type.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\ServiceTypeRepository',
            'App\V1\Repositories\Employee\ServiceTypeRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ServiceTypeFilter',
            'App\V1\Repositories\Query\Employee\ServiceTypeFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ServiceTypeSorter',
            'App\V1\Repositories\Query\Employee\ServiceTypeSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ServiceTypeScopes',
            'App\V1\Repositories\Query\Employee\ServiceTypeScopes'
        );
    }
}