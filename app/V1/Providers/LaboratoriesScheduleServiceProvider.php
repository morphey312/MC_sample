<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class LaboratoriesScheduleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/laboratories-schedule.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\LaboratoriesScheduleRepository',
            'App\V1\Repositories\LaboratoriesScheduleRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LaboratoriesScheduleFilter',
            'App\V1\Repositories\Query\LaboratoriesScheduleFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LaboratoriesScheduleSorter',
            'App\V1\Repositories\Query\LaboratoriesScheduleSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\LaboratoriesScheduleScopes',
            'App\V1\Repositories\Query\LaboratoriesScheduleScopes'
        );
    }
}
