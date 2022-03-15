<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;
use App\V1\Services\TwilioVideoChat;

class VideoChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/video-chat.php'));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\V1\Contracts\Services\VideoChat', function ($app) {
            return new TwilioVideoChat(config('services.twilio'));
        });

        $this->app->bind(
            'App\V1\Contracts\Repositories\VideoChatRepository',
            'App\V1\Repositories\VideoChatRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\VideoChatFilter',
            'App\V1\Repositories\Query\VideoChatFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\VideoChatSorter',
            'App\V1\Repositories\Query\VideoChatSorter'
        );
    }
}
