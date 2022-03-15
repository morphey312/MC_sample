<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class ActionLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/action-log.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\ActionLogRepository',
            'App\V1\Repositories\ActionLogRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ActionLogFilter',
            'App\V1\Repositories\Query\ActionLogFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\ActionLogSorter',
            'App\V1\Repositories\Query\ActionLogSorter'
        );
    }
}