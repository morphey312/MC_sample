<?php

namespace App\V1\Providers\Department;

use Illuminate\Support\ServiceProvider;

class RoomServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/department/room.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Department\RoomRepository',
            'App\V1\Repositories\Department\RoomRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Department\RoomFilter',
            'App\V1\Repositories\Query\Department\RoomFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Department\RoomSorter',
            'App\V1\Repositories\Query\Department\RoomSorter'
        );
    }
}
