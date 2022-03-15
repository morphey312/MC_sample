<?php

namespace App\V1\Providers\Call;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\Call\ProcessLog;
use App\V1\Observers\Call\ProcessLogObserver;

class ProcessLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call/process-log.php'));
        
        ProcessLog::observe(ProcessLogObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Call\ProcessLogRepository',
            'App\V1\Repositories\Call\ProcessLogRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\ProcessLogFilter',
            'App\V1\Repositories\Query\Call\ProcessLogFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\ProcessLogSorter',
            'App\V1\Repositories\Query\Call\ProcessLogSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\ProcessLogScopes',
            'App\V1\Repositories\Query\Call\ProcessLogScopes'
        );
    }
}