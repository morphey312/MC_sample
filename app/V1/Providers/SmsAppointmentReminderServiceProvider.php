<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class SmsAppointmentReminderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/sms-reminders.php'));
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\SmsAppointmentReminderRepository',
            'App\V1\Repositories\SmsAppointmentReminderRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SmsAppointmentReminderFilter',
            'App\V1\Repositories\Query\SmsAppointmentReminderFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SmsAppointmentReminderSorter',
            'App\V1\Repositories\Query\SmsAppointmentReminderSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\SmsAppointmentReminderScopes',
            'App\V1\Repositories\Query\SmsAppointmentReminderScopes'
        );
    }
}
