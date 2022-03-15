<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Traits\Esputnik\DispatchHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Send appointment reminders of the tomorrow day to eSputnik
 *
 * Class SendMissedFirstAppointments
 * @package App\V1\Jobs\Esputnik
 */
class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents, DispatchHandler;

    public $tries = 3;
    protected $date;
    public $timeout = 300;
    protected $template;
    protected $appointment;
    protected $sender;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MailingTemplate $template)
    {
        $this->queue = 'esputnik';
        $this->template = $template;
        $this->date = now()->format('Y-m-d');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $clinics = $this->template->settings_clinics()->get();

        foreach ($clinics as $clinic) {
            $specializationIds = $clinic->specializations()->get()->pluck('specialization_id')->toArray();

            $appointmentStatusId = Appointment\Status::where('system_status', Appointment::STATUS_DELETED)->value('id');

            $todayAppointments = Appointment::query()
                ->where('appointment_status_id', '<>', $appointmentStatusId)
                ->where('clinic_id', '=', $clinic->clinic_id)
                ->whereIn('specialization_id' , $specializationIds)
                ->where('date', $this->date)
                ->with('patient');

            $todayAppointments->chunk(50, function ($chunk){
                foreach ($chunk as $appointment) {

                    $this->appointment = $appointment;
                    $patient = $this->appointment->patient()->firstOrFail();
                    $primaryPhone = $patient->contact_details['primary_phone_number'];
                    $requestData = $this->process();

                    if ($requestData) {
                        $requestData->addParam('sms', $this->normalizePhoneNumber($primaryPhone))
                            ->send();
                    }
                }
            });
        }
    }
}
