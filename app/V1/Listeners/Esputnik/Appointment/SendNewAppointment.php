<?php

namespace App\V1\Listeners\Esputnik\Appointment;

use App\V1\Jobs\Esputnik\SendPatientContact;
use App\V1\Jobs\Esputnik\SendAppointment as Job;

//use Illuminate\Support\Carbon;

class SendNewAppointment
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        //  $appointmentDate = Carbon::parse($event->model->date . $event->model->start);

        // if ($event->model && $appointmentDate->diffInHours($event->model->created_at) >= 2) {
        Job::withChain([
            new SendPatientContact($event->model->id)
        ])
            ->dispatch($event->model->id, $event->model->date, $event->model->start)->delay(now()
                ->addMinutes(20));
    }
    //  }
}
