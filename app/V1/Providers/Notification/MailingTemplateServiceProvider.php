<?php

namespace App\V1\Providers\Notification;

use Illuminate\Support\ServiceProvider;

class MailingTemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/notification/mailing-template.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Notification\MailingTemplateRepository',
            'App\V1\Repositories\Notification\MailingTemplateRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingTemplateFilter',
            'App\V1\Repositories\Query\Notification\MailingTemplateFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingTemplateSorter',
            'App\V1\Repositories\Query\Notification\MailingTemplateSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Notification\MailingSetting\NotificationTemplateSettingClinicRepository',
            'App\V1\Repositories\Notification\MailingSetting\NotificationMailingTemplateSettingClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicFilter',
            'App\V1\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicSorter',
            'App\V1\Repositories\Query\Notification\MailingSetting\NotificationMailingTemplateSettingClinicSorter'
        );

    }
}
