<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class EducationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/education.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\EducationRepository',
            'App\V1\Repositories\Employee\EducationRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\EducationFilter',
            'App\V1\Repositories\Query\Employee\EducationFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\EducationSorter',
            'App\V1\Repositories\Query\Employee\EducationSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\EducationScopes',
            'App\V1\Repositories\Query\Employee\EducationScopes'
        );
    }
}