<?php


namespace App\V1\Jobs\Esputnik;


use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Facades\Esputnik;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Repositories\HandbookRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UpdatePatientContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $tries = 3;
    public $timeout = 30;
    public $appointment, $otherVisit;
    protected $template;


    public function __construct($appointment, $otherVisit, MailingTemplate $template)
    {
        $this->queue = 'esputnik';
        $this->appointment = $appointment;
        $this->otherVisit = $otherVisit;
        $this->template = $template;

    }

    /**
     *  Update patient contact if his appointment is canceled
     */
    public function handle()
    {
        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);

        $patient = $this->appointment->patient;

        $contact->newContact()
            ->setFirstName($patient->firstname);

        if ($patient->email) {
            $contact->addChannel('email', $patient->email->value);
        }

        if ($patient->primary_phone) {
            $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
        }

        $contact->setOption('dedupeOn', 'email_or_sms');
        $contact->setOption('groupNames', ['UpdatedVisit']);
        $contact->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

        if ($this->otherVisit) {

            $contact->addCustomField(166955, $this->appointment->specialization->name);
            $contact->addCustomField(166954, sprintf('%sT%s',
                Carbon::parse($this->appointment->date)->format('Y-m-d'),
                Carbon::parse($this->appointment->start)->format('H:i')
            ));

            $city = app(HandbookRepository::class)->findCity($this->appointment->clinic->city, false);

            if ($city)
                $contact->setField('address.town', $city->value);

            if ($this->appointment->is_first)
                $contact->addCustomField(178702, 'Первичный прием');

        } else {
            $contact->addCustomField(166955, '');
            $contact->addCustomField(166954, '');
            $contact->addCustomField(178702, '');
        }

        $contact->storeNewContact();
        $contact->send();

    }
}
