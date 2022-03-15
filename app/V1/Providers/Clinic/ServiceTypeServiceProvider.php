<?php

namespace App\V1\Providers\Clinic;

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
        $this->loadRoutesFrom(base_path('routes/modules/v1/clinic/service-type.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Clinic\ServiceTypeRepository',
            'App\V1\Repositories\Clinic\ServiceTypeRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\ServiceTypeFilter',
            'App\V1\Repositories\Query\Clinic\ServiceTypeFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\ServiceTypeSorter',
            'App\V1\Repositories\Query\Clinic\ServiceTypeSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Clinic\ServiceTypeScopes',
            'App\V1\Repositories\Query\Clinic\ServiceTypeScopes'
        );
    }
}