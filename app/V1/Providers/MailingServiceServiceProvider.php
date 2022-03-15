<?php

namespace App\V1\Providers;

use Illuminate\Support\ServiceProvider;

class MailingServiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
       $this->app->bind(
            'send-appointment',
            'App\V1\Jobs\Esputnik\SendAppointment'
        );

        $this->app->bind(
            'send-appointment-completed-has-next-visit',
            'App\V1\Jobs\Esputnik\SendAppointmentCompletedHasNextVisit'
        );

        $this->app->bind(
            'send-appointment-reminder',
            'App\V1\Jobs\Esputnik\SendAppointmentReminder'
        );

        $this->app->bind(
            'send-contacts-primary-appointment',
            'App\V1\Jobs\Esputnik\SendContactsPrimaryAppointment'
        );

        $this->app->bind(
            'send-missed-first-appointments',
            'App\V1\Jobs\Esputnik\SendMissedFirstAppointments'
        );

        $this->app->bind(
            'send-missed-next-appointments',
            'App\V1\Jobs\Esputnik\SendMissedNextAppointments'
        );

        $this->app->bind(
            'send-phone-not-reached-contacts',
            'App\V1\Jobs\Esputnik\SendPhoneNotReachedContacts'
        );

        $this->app->bind(
            'send-phone-reached-without-appointment-contacts',
            'App\V1\Jobs\Esputnik\SendPhoneReachedWithoutAppointmentContacts'
        );

        $this->app->bind(
            'send-confirmed-data',
            'App\V1\Jobs\Esputnik\SendConfirmedData'
        );

        $this->app->bind(
            'send-email-status',
            'App\V1\Jobs\Esputnik\SendEmailStatus'
        );
    }
}
