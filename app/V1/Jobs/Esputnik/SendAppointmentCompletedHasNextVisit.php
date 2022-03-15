<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Facades\Esputnik;
use App\V1\Models\Appointment;
use App\V1\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class SendAppointmentCompletedHasNextVisit
 * @package App\V1\Jobs\Esputnik
 */
class SendAppointmentCompletedHasNextVisit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $tries = 3;

    protected $date;

    public $timeout = 300;
    protected $template;


    /**
     * Execute the job.
     *
     * Send patients contacts to eSputnik, if they have completed appointments and with/orNot next visit date
     *
     * @return void
     */
    public function __construct($template = null)
    {
        $this->queue = 'esputnik';
        $this->date = now()->subDay();
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $clinicsFromTemplate = $this->template->settings_clinics()->get()->pluck('clinic_id')->toArray();
        $appointmentStatusId = Appointment\Status::where('system_status', Appointment::STATUS_COMPLETED)->value('id');

        $todayAppointmentsWithNextVisit = Appointment::query()
            ->select('appointments.patient_id', 'appointments.date','card_records.recordable_type', 'patient_next_visit.next_visit_date')
            ->leftJoin('card_records', 'appointments.id', '=', 'card_records.appointment_id')
            ->leftJoin('patient_next_visit', 'card_records.recordable_id', '=', 'patient_next_visit.id')
            ->where('appointments.appointment_status_id', $appointmentStatusId)
            ->where('appointments.date', $this->date->format('Y-m-d'))
            ->whereIn('appointments.clinic_id', $clinicsFromTemplate);


        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);

        $contact
            ->setOption('dedupeOn', 'email_or_sms')
            ->setOption('groupNames', ['VisitFinish'])
            ->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

        $todayAppointmentsWithNextVisit->chunk(50, function ($chunk) use($contact) {

            $patients = Patient::query()
                    ->whereIn('id', $chunk->pluck('patient_id')->toArray())
                    ->with('contacts')
                    ->get();
            foreach ($patients as $patient) {
                $date = $chunk->filter(function ($patientID) use ($patient){
                    return $patientID->id === $patient->id;
                })->first();

                if (!$patient->email && !$patient->primary_phone) {
                    continue;
                }

                $contact
                    ->newContact()
                    ->setFirstName($patient->firstname)
                    ->addCustomField(166959, $date->date);

                if ($patient->email) {
                    $contact->addChannel('email', $patient->email->value);
                }

                if ($patient->primary_phone) {
                    $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
                }

                if ($date->next_visit_date) {
                    $contact->addCustomField(177180, $date->next_visit_date);
                }

                $contact->storeNewContact();
            };
        });


       $contact->send();

    }
}
