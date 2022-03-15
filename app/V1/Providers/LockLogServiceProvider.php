<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\DaySheet\LockLog;

class LockLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/locklog.php'));

        LockLog::observe(RecordChangeObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\LockLogRepository',
            'App\V1\Repositories\LockLogRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LockLogFilter',
            'App\V1\Repositories\Query\LockLogFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LockLogSorter',
            'App\V1\Repositories\Query\LockLogSorter'
        );
    }
}
