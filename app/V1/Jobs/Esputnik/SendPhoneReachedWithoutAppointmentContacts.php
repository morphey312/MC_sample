<?php

namespace App\V1\Jobs\Esputnik;

use App\V1\Console\Commands\VoipControl\Concerns\HandleEvents;
use App\V1\Facades\Esputnik;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Patient;
use App\V1\Models\SiteEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * WIP!
 * Class SendPhoneReachedWithoutAppointmentContacts
 * @package App\V1\Jobs\Esputnik
 */
class SendPhoneReachedWithoutAppointmentContacts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandleEvents;

    public $tries = 3;

    protected $date;
    protected $template;

    public $timeout = 300;

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
        $ids = $this->getAffectedPatientIds();

        if ($ids) {
            $patientQuery = Patient::query()
                ->whereIn('id', $ids)
                ->whereHas('clinics', function($querry) use($clinicsFromTemplate) {
                    $querry->whereIn('id', $clinicsFromTemplate);
                })
                ->with('contacts');

            $provider = $this->template->provider()->get()->first();
            $contact = Esputnik::newContact($provider);

                /**
                 * @var \App\V1\Contracts\Services\Esputnik\Contact $contact
                 */
                $contact
                    ->setOption('dedupeOn', 'email_or_sms')
                    ->setOption('eventKeyForNewContacts', 'NewFromCRM', true)
                    ->setOption('groupNames', ['CallNoAppointment']);

                $patientQuery->chunk(20, function($patients) use ($contact){
                    foreach ($patients as $patient) {
                        if(!$patient->email && !$patient->primary_phone) continue;

                        $contact->newContact()
                            ->setFirstName($patient->firstname);

                        if ($patient->email) {
                            $contact->addChannel('email', $patient->email->value);
                        }

                        if ($patient->primary_phone) {
                            $contact->addChannel('sms', $this->normalizePhoneNumber($patient->contact_details['primary_phone_number']));
                        }

                        $contact
                            ->addCustomField(176470, $this->date->toDateString());

                        $contact->storeNewContact();
                    }
                });

                $contact->send();
        }
    }

    protected function getAffectedPatientIds()
    {

        $siteEnquiries = SiteEnquiry::query()
            ->where('created_at', '>=', $this->date->startOfDay()->toDateTimeString())
            ->where('created_at', '<=', $this->date->endOfDay()->toDateTimeString())
            ->whereNotNull('specialization_id')
            ->whereNotNull('patient_id')
            ->whereNotExists(function ($query) {
                $query
                    ->from('appointments')
                    ->select('appointments.id')
                    ->whereColumn('appointments.patient_id', 'site_enquiries.patient_id')
                    ->whereColumn('appointments.specialization_id', 'site_enquiries.specialization_id')
                    ->where('date', $this->date->toDateString());
            })
            ->whereExists(function ($query) {
                $query
                    ->from('calls')
                    ->select('calls.*')
                    ->join('call_results', 'calls.call_result_id', '=', 'call_results.id')
                    ->whereColumn('calls.specialization_id', 'site_enquiries.specialization_id')
                    ->whereColumn('calls.contact_id', 'site_enquiries.patient_id')
                    ->where('contact_type', Patient::RELATION_TYPE)
                    ->where('date', $this->date->toDateString())
                    ->where('call_results.esputnik_no_answer', 0)
                    ->where('call_results.for_wait_list', 0);
            });

        return $siteEnquiries->pluck('site_enquiries.patient_id')->unique();
    }
}
