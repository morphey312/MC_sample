<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/notification.php'));

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Services\MessengerService',
            'App\V1\Services\MessengerService'
        );

        $this->app->bind(
            'messenger',
            'App\V1\Contracts\Services\MessengerService'
        );

        $this->app->bind(
            'App\V1\Contracts\Services\MailingService',
            'App\V1\Services\MailingService'
        );

        $this->app->bind(
            'mailing-messenger',
            'App\V1\Contracts\Services\MailingService'
        );

        $this->app->bind(
            'Illuminate\Notifications\Channels\BroadcastChannel',
            'App\V1\Notifications\Channels\BroadcastChannel'
        );
        $this->app->bind(
            'App\V1\Contracts\Repositories\NotificationRepository',
            'App\V1\Repositories\NotificationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\NotificationFilter',
            'App\V1\Repositories\Query\NotificationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\NotificationSorter',
            'App\V1\Repositories\Query\NotificationSorter'
        );
    }
}
