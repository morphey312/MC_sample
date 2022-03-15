<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Models\Appointment;
use App\V1\Traits\Esputnik\DispatchHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendAppointment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DispatchHandler, HandleEvents;

    public $tries = 3;
    public $timeout = 30;
    public $message;
    public $template;
    public $appointment;
    public $id, $date, $start, $end;

    /**
     * Create a new job instance.
     */
    public function __construct($template = null, $message = null)
    {
        $this->queue = 'esputnik';
        $this->message = $message;
        $this->template = $template;
        if ($this->message) {
            $this->appointment = $this->message->getAppointment();
            $this->id = $this->appointment->id;
            $this->date = $this->appointment->date;
            $this->start = $this->appointment->start;
            $this->end = $this->appointment->end;
        }
    }

    /**
     * Execute the job.
     *
     * Send email to patient if  changed the date of  appointment
     *
     * @return void
     */
    public function handle()
    {

        if ($this->appointment->is_deleted == true || $this->appointment->status->system_status == Appointment::STATUS_DELETED) {

            $otherVisits = Appointment::where([
                ['patient_id', $this->appointment->patient->id],
                ['date', '>=', now()->toDateString()],
                ['is_deleted', false]
            ])->whereIn('appointment_status_id', function ($query) {
                $query
                    ->select('id')
                    ->from('appointment_statuses')
                    ->where('system_status', Appointment::STATUS_SIGNED_UP);
            })->get();

            if ($otherVisits->isNotEmpty())
                dispatch(new UpdatePatientContact($otherVisits->first(), true, $this->template));
            else
                dispatch(new UpdatePatientContact($this->appointment, false, $this->template));
        }

        $this->appointment = Appointment::with([
            'patient.contacts',
            'specialization',
            'analysis_results',
            'appointment_services.service.specialization',
            'doctor',
            'clinic'
        ])->find($this->id);

        if ($this->appointment->date == $this->date &&
            $this->appointment->start == $this->start . ':00'&&
            $this->appointment->is_deleted == 0 &&
            $this->appointment->status->system_status != Appointment::STATUS_DELETED
        ) {
            Log::channel('esputnik_test')->info('result5-TEST', [
                'result5' => 'passed'
            ]);

            $patient = $this->appointment->patient()->firstOrFail();
            $primaryPhone = $patient->contact_details['primary_phone_number'];

            $requestData = $this->process();

            if ($requestData) {
                $requestData->addParam('sms', $this->normalizePhoneNumber($primaryPhone))
                    ->send();
                dispatch(new SendPatientContact($this->id, $this->template));
            }
        }
    }
}
