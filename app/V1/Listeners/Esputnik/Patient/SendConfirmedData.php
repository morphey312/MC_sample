<?php

namespace App\V1\Listeners\Esputnik\Patient;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Patient;
use App\V1\Repositories\HandbookRepository;
use App\V1\Traits\Esputnik\DispatchHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendConfirmedData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, DispatchHandler, HandleEvents;

    public $tries = 3;

    public $queue = 'esputnik';
    public $template;
    public $message;


    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct($template = null, $message = null)
    {
        $this->queue = 'esputnik';
        $this->message = $message;
        $this->template = $template;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var Contact $contact
         */
        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);

        $patient = $this->message->getPatient();
        $isChanged = false;

        if(
        $patient->isDirty(['firstname', 'birthday', 'gender', 'is_confirmed'])
        || $patient->hasChangedContacts
        ) {
        $isChanged = true;
        }

        if ($isChanged) {
            if (
                $patient->primary_phone
                && $patientClinic = $patient->patient->getClinicOfContact($patient->contact_details['primary_phone_number'])
            ) {
                $patientCity = app(HandbookRepository::class)->findCity($patientClinic->city, false);
            }

            $contact->setOption('groupNames', ['General']);

            $contact->newContact()
                ->setFirstName($patient->firstname);

            if ($patient->email) {
                $contact->addChannel('email', $patient->email->value);
            }

            if ($patient->primary_phone) {
                $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
            }

            if (isset($patientCity)) {
                $contact->setField('address.town', $patientCity->value);
            }

            if ($patient->birthday) {
                $contact->addCustomField(154885, $patient->birthday->format('Y-m-d'));
            }

            if ($patient->gender) {
                $contact->addCustomField(154886, $this->getContactGender($patient));
            }

            $contact->storeNewContact();

            return $contact->send();
        }
    }

    protected function getContactGender($patient)
    {
        switch ($patient->gender) {
            case Patient::GENDER_MALE:
                return 'М';
                break;
            case Patient::GENDER_FEMALE:
                return 'Ж';
                break;
            default:
                return null;
        }
    }
}
