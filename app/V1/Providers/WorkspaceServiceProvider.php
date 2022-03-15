<?php

namespace App\V1\Providers;

use App\V1\Observers\Audit\Workspace\WorkspaceAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Workspace;

class WorkspaceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/workspace.php'));

        Workspace::observe(WorkspaceAudit::class);
        Workspace::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\WorkspaceRepository',
            'App\V1\Repositories\WorkspaceRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\WorkspaceFilter',
            'App\V1\Repositories\Query\WorkspaceFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\WorkspaceSorter',
            'App\V1\Repositories\Query\WorkspaceSorter'
        );
    }
}
