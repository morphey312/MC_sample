<?php

namespace App\V1\Providers;

use App\V1\Observers\Appointment\ChangeVisitObserver;
use Illuminate\Support\ServiceProvider;
use App\V1\Models\Appointment;
use App\V1\Observers\Audit\AppointmentAudit;
use App\V1\Observers\Appointment\ChainObserver;
use App\V1\Observers\Appointment\CallRequestObserver;
use App\V1\Observers\Appointment\AmbulanceStatusObserver;
use App\V1\Observers\Appointment\PatientAppointmentStatusObserver;
use App\V1\Observers\Elastic\AppointmentObserver as ElasticAppointmentObserver;
use App\V1\Observers\AppointmentSmsReminderObserver;
use App\V1\Observers\AppointmentObserver as AppointmentObserver;

class AppointmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/appointment.php'));

        Appointment::observe(AppointmentAudit::class);
        Appointment::observe(ChainObserver::class);
        Appointment::observe(CallRequestObserver::class);
        Appointment::observe(ElasticAppointmentObserver::class);
        Appointment::observe(AppointmentSmsReminderObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Appointment::observe(PatientAppointmentStatusObserver::class);
        Appointment::observe(AmbulanceStatusObserver::class);

    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind(
            'App\V1\Contracts\Repositories\AppointmentRepository',
            'App\V1\Repositories\AppointmentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\AppointmentFilter',
            'App\V1\Repositories\Query\AppointmentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\AppointmentSorter',
            'App\V1\Repositories\Query\AppointmentSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\AppointmentScopes',
            'App\V1\Repositories\Query\AppointmentScopes'
        );
    }
}
