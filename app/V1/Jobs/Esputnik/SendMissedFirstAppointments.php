<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Send missed first appointments of the past day to eSputnik
 *
 * Class SendMissedFirstAppointments
 * @package App\V1\Jobs\Esputnik
 */
class SendMissedFirstAppointments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $tries = 3;

    protected $date;

    public $timeout = 300;

    protected $template;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MailingTemplate $template)
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
        $statuses =  $this->template->statuses()->get()->pluck('id')->toArray();
        $clinicsFromTemplate = $this->template->settings_clinics()->get()->pluck('clinic_id')->toArray();
        $appointmentStatusId = Appointment\Status::where('system_status', Appointment::STATUS_DIDNT_COME)->value('id');

        $todayAppointments = Appointment::query()
            ->select('patient_id', 'date', 'status_reason_id')
            ->where('appointment_status_id', $appointmentStatusId)
            ->where('is_first', 1)
            ->whereIn('clinic_id', $clinicsFromTemplate)
            ->where('date', $this->date->format('Y-m-d'));

        if (! empty($statuses)) {
            $todayAppointments->whereIn('status_reason_id', $statuses);
        }

        /**
         * @var Contact $contact
         */
        $provider = $this->template->provider()->get()->first();
        $contact = Esputnik::newContact($provider);

        $contact
            ->setOption('dedupeOn', 'email_or_sms')
            ->setOption('groupNames', ['VisitMissed'])
            ->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

        $todayAppointments->chunk(50, function ($chunk) use ($contact){

            $patients = Patient::query()
                ->whereIn('id', $chunk->pluck('patient_id')->toArray())
                ->with('contacts')
                ->get();

            foreach ($patients as $patient) {
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
