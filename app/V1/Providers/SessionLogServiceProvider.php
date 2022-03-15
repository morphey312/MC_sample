<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\SessionLog;
use App\V1\Observers\SessionLogObserver;

class SessionLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        SessionLog::observe(SessionLogObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\SessionLogRepository',
            'App\V1\Repositories\SessionLogRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SessionLogFilter',
            'App\V1\Repositories\Query\SessionLogFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SessionLogSorter',
            'App\V1\Repositories\Query\SessionLogSorter'
        );
    }
}