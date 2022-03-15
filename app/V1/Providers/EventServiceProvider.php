<?php

namespace App\V1\Providers;

use App\V1\Events\Analysis\Result\AnalysisResultSaved;
use App\V1\Events\Appointment\AppointmentCreated;
use App\V1\Events\Appointment\AppointmentUpdated;
use App\V1\Events\Patient\PatientSaved;
use App\V1\Listeners\Esputnik\Analysis\SendEmailStatus;
use App\V1\Listeners\Esputnik\Appointment\SendNewAppointment;
use App\V1\Listeners\Esputnik\Patient\SendConfirmedData;
use App\V1\Listeners\Esputnik\Appointment\SendPatientContact;
use App\V1\Listeners\Esputnik\Appointment\SendData;
use App\V1\Listeners\Esputnik\Appointment\SendResult;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    ];

    /**
     * The subscriber mappings for the application.
     *
     * @var string[]
     */
    protected $subscribe = [
        'App\V1\Listeners\Subscribers\EmployeeEventSubscriber',
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

       Event::listen(
           Registered::class,
           SendEmailVerificationNotification::class
       );
    }
}
