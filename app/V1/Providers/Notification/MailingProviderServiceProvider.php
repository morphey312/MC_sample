<?php

namespace App\V1\Providers\Notification;

use Illuminate\Support\ServiceProvider;

class MailingProviderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/notification/mailing-provider.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Notification\MailingProviderRepository',
            'App\V1\Repositories\Notification\MailingProviderRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingProviderFilter',
            'App\V1\Repositories\Query\Notification\MailingProviderFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingProviderSorter',
            'App\V1\Repositories\Query\Notification\MailingProviderSorter'
        );
    }
}
