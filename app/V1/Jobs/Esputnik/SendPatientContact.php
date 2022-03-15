<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Repositories\HandbookRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;

class SendPatientContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $id;

    protected $tries = 3;
    protected $timeout = 30;
    protected $template;

    /**
     * Create a new job instance.
     *
     * @param int $id
     */
    public function __construct(int $id, MailingTemplate $template)
    {
        $this->queue = 'esputnik';
        $this->template = $template;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appointment = Appointment::find($this->id);
        /**
         * @var Contact $contact
         */
        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);
        $patient = $appointment->patient;
        $patientClinic = $patientCity = null;

        if ($patient->primary_phone && $patientClinic = $patient->getClinicOfContact($patient->contact_details['primary_phone_number'])) {
            $patientCity = app(HandbookRepository::class)->findCity($patientClinic->city, false);
        }

        $contact->setOption('dedupeOn', 'email_or_sms');
        $contact->setOption('groupNames', ['Visit']);
        $contact->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

        $contact->newContact()
            ->setFirstName($patient->firstname);

        if ($patient->email) {
            $contact->addChannel('email', $patient->email->value);
        }

        if ($patient->primary_phone) {
            $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
        }

        if ($patientClinic) {
            $contact->addCustomField(166860, $patientClinic->name);
        }

        if ($patientCity) {
            $contact->setField('address.town', $patientCity->value);
        }

        $contact->addCustomField(166955, $appointment->specialization->name);

        $contact->addCustomField(166954, sprintf('%sT%s',
            Carbon::parse($appointment->date)->format('Y-m-d'),
            Carbon::parse($appointment->start)->format('H:i')
        ));

        $contact->storeNewContact();

        return $contact->send();
    }
}
