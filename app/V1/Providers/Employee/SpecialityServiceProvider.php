<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class SpecialityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/speciality.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\SpecialityRepository',
            'App\V1\Repositories\Employee\SpecialityRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\SpecialityFilter',
            'App\V1\Repositories\Query\Employee\SpecialityFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\SpecialitySorter',
            'App\V1\Repositories\Query\Employee\SpecialitySorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\SpecialityScopes',
            'App\V1\Repositories\Query\Employee\SpecialityScopes'
        );
    }
}