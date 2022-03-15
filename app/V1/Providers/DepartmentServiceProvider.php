<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class DepartmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/department.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DepartmentRepository',
            'App\V1\Repositories\DepartmentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DepartmentFilter',
            'App\V1\Repositories\Query\DepartmentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DepartmentSorter',
            'App\V1\Repositories\Query\DepartmentSorter'
        );
    }
}
