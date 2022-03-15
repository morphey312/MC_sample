<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\RoleAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Role;

class RoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/role.php'));

        Role::observe(RecordChangeObserver::class);
        Role::observe(RoleAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\RoleRepository',
            'App\V1\Repositories\RoleRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\RoleFilter',
            'App\V1\Repositories\Query\RoleFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\RoleSorter',
            'App\V1\Repositories\Query\RoleSorter'
        );
    }
}
