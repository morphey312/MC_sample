<?php

namespace App\V1\Providers\Employee;

use Illuminate\Support\ServiceProvider;

class QualificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/qualification.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\QualificationRepository',
            'App\V1\Repositories\Employee\QualificationRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\QualificationFilter',
            'App\V1\Repositories\Query\Employee\QualificationFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\QualificationSorter',
            'App\V1\Repositories\Query\Employee\QualificationSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\QualificationScopes',
            'App\V1\Repositories\Query\Employee\QualificationScopes'
        );
    }
}