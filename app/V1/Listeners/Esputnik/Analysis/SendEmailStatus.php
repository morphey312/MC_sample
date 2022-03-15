<?php

namespace App\V1\Listeners\Esputnik\Analysis;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Analysis\Result;
use App\V1\Traits\Esputnik\DispatchHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SendEmailStatus implements ShouldQueue
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
        $result = $this->message->getResult();
        $status = $result->status;
        $isDirty = true;

        $hasUnsentResults = Result::query()
            ->select('id', 'patient_id', 'status')
            ->where('patient_id', $result->patient_id)
            ->whereNotIn('status',  [
                Result::STATUS_EMAIL_SENT,
                Result::STATUS_ASSIGNED_BUT_NOT_BE_TEST
            ])
            ->exists();

        if($hasUnsentResults) {
            $isDirty = false;
        }

        if($isDirty && $status === Result::STATUS_EMAIL_SENT) {
            /**
             * @var Contact $contact
             */
            $provider = $this->template->provider()->get()->first();
            $contact = Esputnik::newContact($provider);
            $patient = $result->patient;

            $contact->setOption('dedupeOn', 'email_or_sms');
            $contact->setOption('eventKeyForNewContacts', 'NewFromCRM', true);
            $contact->setOption('groupNames', ['TestAnalysis']);

            $contact->newContact()
                ->setFirstName($patient->firstname);

            if($patient->email) {
                $contact->addChannel('email', $patient->email->value);
            }

            if ($patient->primary_phone) {
                $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
            }

            $contact->addCustomField(166986, Carbon::parse($result->date_sent_email)->format('Y-m-d'));

            $contact->storeNewContact();

            return $contact->send();
        }
    }
}
