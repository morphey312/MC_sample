<?php

namespace App\V1\Providers\Call;

use App\V1\Models\Call\CallLog;
use App\V1\Observers\CallLogObserver;
use Illuminate\Support\ServiceProvider;

class CallLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/call/call-log.php'));
        CallLog::observe(CallLogObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Call\CallLogRepository',
            'App\V1\Repositories\Call\CallLogRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\CallLogFilter',
            'App\V1\Repositories\Query\Call\CallLogFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\CallLogSorter',
            'App\V1\Repositories\Query\Call\CallLogSorter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Call\CallLogScopes',
            'App\V1\Repositories\Query\Call\CallLogScopes'
        );
    }
}