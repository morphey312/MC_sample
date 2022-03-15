<?php

namespace App\V1\Providers\Employee;

use App\V1\Observers\HrPortal\CreatePositionObserver;
use Illuminate\Support\ServiceProvider;
use App\V1\Observers\RecordChangeObserver;
use App\V1\Models\Employee\Position;

class EmployeePositionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/employee/position.php'));

        Position::observe(RecordChangeObserver::class);
        Position::observe(CreatePositionObserver::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Employee\PositionRepository',
            'App\V1\Repositories\Employee\PositionRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\PositionFilter',
            'App\V1\Repositories\Query\Employee\PositionFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Employee\PositionSorter',
            'App\V1\Repositories\Query\Employee\PositionSorter'
        );
    }
}
