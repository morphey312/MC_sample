<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Models\DaySheet;
use App\V1\Observers\Audit\DaySheetAudit;
use App\V1\Observers\DaySheetObserver;
use App\V1\Models\DaySheet\Lock;
use App\V1\Observers\LockObserver;

class DaySheetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/day-sheet.php'));

        DaySheet::observe(DaySheetAudit::class);
        DaySheet::observe(DaySheetObserver::class);
        Lock::observe(LockObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\DaySheetRepository',
            'App\V1\Repositories\DaySheetRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheetFilter',
            'App\V1\Repositories\Query\DaySheetFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheetSorter',
            'App\V1\Repositories\Query\DaySheetSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\DaySheet\LockRepository',
            'App\V1\Repositories\DaySheet\LockRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheet\LockFilter',
            'App\V1\Repositories\Query\DaySheet\LockFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\DaySheet\LockSorter',
            'App\V1\Repositories\Query\DaySheet\LockSorter'
        );
    }
}
