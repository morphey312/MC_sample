<?php

namespace App\V1\Providers\Notification;

use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/notification/template.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\Notification\TemplateRepository',
            'App\V1\Repositories\Notification\TemplateRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\TemplateFilter',
            'App\V1\Repositories\Query\Notification\TemplateFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Notification\TemplateSorter',
            'App\V1\Repositories\Query\Notification\TemplateSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\NotificationTemplateSettingClinicRepository',
            'App\V1\Repositories\NotificationTemplateSettingClinicRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\NotificationTemplateSettingClinicFilter',
            'App\V1\Repositories\Query\NotificationTemplateSettingClinicFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\NotificationTemplateSettingClinicSorter',
            'App\V1\Repositories\Query\NotificationTemplateSettingClinicSorter'
        );

    }
}
