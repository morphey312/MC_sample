<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Contracts\Services\Esputnik\Contact;
use App\V1\Facades\Esputnik;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;


class SendContactsPrimaryAppointment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

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
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appointmentsQuery = Appointment::query();
        $clinicsFromTemplate = $this->template->settings_clinics()->get()->pluck('clinic_id')->toArray();

        $appointmentsQuery
            ->where([
                ['appointments.date', now()->subDay()->toDateString()],
                ['appointments.is_first', 1],
            ])
            ->whereIn('clinic_id', $clinicsFromTemplate);

        $appointmentsQuery->chunk(100, function ($appointments) {
            /**
             * @var Contact $contact
             */
            $provider = $this->template->provider()->get()->first();
            $contact = Esputnik::newContact($provider);

            $contact
                ->setOption('dedupeOn', 'email_or_sms')
                ->setOption('contactFields', ['email', 'sms'])
                ->setOption('groupNames', ['FirstTime'])
                ->setOption('eventKeyForNewContacts', 'NewFromCRM', true);

            foreach ($appointments as $appointment) {

                $contact
                    ->newContact()
                    ->setFirstName($appointment->patient->firstname, true);


                $email = '';
                $phone = '';
                foreach ($appointment->patient->contacts as $contacts) {
                    if ($contacts->type == 'email')
                        $email = $contacts->value;
                    if ($contacts->type == 'phone')
                        $phone = $contacts->value;
                }

                if ($email) {
                    $contact->addChannel('email', $email);
                }

                if ($phone) {
                    $contact->addChannel('sms', '38' . $phone);
                }

                $contact
                    ->addCustomField(187982, 'да')
                    ->storeNewContact();

            }
            $contact->send();
        });
    }
}
