<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/chat.php'));

    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Chat\RoomRepository',
            'App\V1\Repositories\Chat\RoomRepository'
        );
        $this->app->bind(
            'App\V1\Contracts\Repositories\Chat\MessageRepository',
            'App\V1\Repositories\Chat\MessageRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Chat\RoomFilter',
            'App\V1\Repositories\Query\Chat\RoomFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Chat\MessageFilter',
            'App\V1\Repositories\Query\Chat\MessageFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Chat\RoomSorter',
            'App\V1\Repositories\Query\Chat\RoomSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Chat\MessageSorter',
            'App\V1\Repositories\Query\Chat\MessageSorter'
        );
    }
}
