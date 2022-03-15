<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class ScienceDegreeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/science-degree.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\ScienceDegreeRepository',
            'App\V1\Repositories\Employee\ScienceDegreeRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ScienceDegreeFilter',
            'App\V1\Repositories\Query\Employee\ScienceDegreeFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ScienceDegreeSorter',
            'App\V1\Repositories\Query\Employee\ScienceDegreeSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\ScienceDegreeScopes',
            'App\V1\Repositories\Query\Employee\ScienceDegreeScopes'
        );
    }
}