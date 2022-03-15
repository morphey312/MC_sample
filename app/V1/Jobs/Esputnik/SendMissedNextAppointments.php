<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Send missed next appointments after first visit of the past day to eSputnik
 *
 * Class SendMissedFirstAppointments
 * @package App\V1\Jobs\Esputnik
 */
class SendMissedNextAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $tries = 3;

    protected $date;
    protected $template;

    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MailingTemplate $template)
    {
        $this->queue = 'esputnik';
        $this->template = $template;
        $this->date = now()->subDay();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $clinicsFromTemplate = $this->template->settings_clinics()->get()->pluck('clinic_id')->toArray();


        $nextVisit = Patient\Card\NextVisit::query()
            ->select('patients.id')
            ->join('call_requests', 'patient_next_visit.call_request_id', '=', 'call_requests.id')
            ->join('patients', 'call_requests.patient_id', '=', 'patients.id')
            ->leftJoin('appointments', 'call_requests.appointment_id', '=', 'appointments.id')
            ->whereIn('call_requests.clinic_id', $clinicsFromTemplate)
            ->whereNull('call_requests.appointment_id')
            ->where('patient_next_visit.next_visit_date', $this->date->format('Y-m-d'));
        /**
         * @var Contact $contact
         */
        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);

        $contact
            ->setOption('dedupeOn', 'email_or_sms')
            ->setOption('groupNames', ['VisitMissed'])
            ->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

        $nextVisit->chunk(50, function ($chunk) use ($contact){

            $patients = Patient::query()
                ->whereIn('id', $chunk->pluck('id')->toArray())
                ->with('contacts')
                ->get();

            foreach ($patients as $patient) {
                $data = $chunk->filter(function ($patientID) use ($patient){
                    return $patientID->id === $patient->id;
                })->first();


                $contact
                    ->newContact()
                    ->setFirstName($patient->firstname)
                    ->addCustomField(166958, 1);


                if ($patient->email) {
                    $contact->addChannel('email', $patient->email->value);
                }

                if ($patient->primary_phone) {
                    $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
                }

                $contact->storeNewContact();
            }

            $contact->send();
        });
    }
}
