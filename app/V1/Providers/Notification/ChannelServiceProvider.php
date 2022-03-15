<?php

namespace App\V1\Providers\Notification;

use Illuminate\Support\ServiceProvider;

class ChannelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/notification/channel.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Notification\ChannelRepository',
            'App\V1\Repositories\Notification\ChannelRepository'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\ChannelFilter',
            'App\V1\Repositories\Query\Notification\ChannelFilter'
        );
        
        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\ChannelSorter',
            'App\V1\Repositories\Query\Notification\ChannelSorter'
        );
    }
}