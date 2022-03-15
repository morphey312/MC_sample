<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/permissions.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\PermissionsService',
            'App\V1\Services\PermissionsService'
        );

        $this->app->bind(
            'permissions', 
            'App\V1\Contracts\Services\PermissionsService'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\PermissionRepository',
            'App\V1\Repositories\PermissionRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PermissionFilter',
            'App\V1\Repositories\Query\PermissionFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\PermissionSorter',
            'App\V1\Repositories\Query\PermissionSorter'
        );
    }
}