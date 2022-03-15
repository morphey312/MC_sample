<?php

namespace App\V1\Providers\Department\Room;

use Illuminate\Support\ServiceProvider;

class OccupationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/department/room/occupation.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Department\Room\OccupationRepository',
            'App\V1\Repositories\Department\Room\OccupationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Department\Room\OccupationFilter',
            'App\V1\Repositories\Query\Department\Room\OccupationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Department\Room\OccupationSorter',
            'App\V1\Repositories\Query\Department\Room\OccupationSorter'
        );
    }
}
