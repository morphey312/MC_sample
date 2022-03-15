<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Patient\AppointmentDocument;
use App\V1\Models\Patient\ClientIds;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Patient\Relative as PatientRelative;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\InsuranceCompany\Act\Service;
use App\V1\Repositories\Appointment\ServiceRepository as AppointmentServiceRepository;
use App\V1\Traits\Models\HasCachedAttributes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class Patient extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasCachedAttributes;

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    const STATUS_PATIENT = 'patient';
    const STATUS_GUEST = 'guest';

    const INSURANCE_YES = 'yes';
    const INSURANCE_NO = 'no';
    const INSURANCE_NA = 'n_a';

    const RELATION_TYPE = 'patient';

    const ASSIGNMENTS_MAX_LIFE_IN_DAYS = 180;

    /**
     * @var array
     */
    protected $fillable = [
        'ehealth_patient_id',
        'firstname',
        'lastname',
        'middlename',
        'passport_no',
        'gender',
        'status',
        'med_insurance',
        'location',
        'street',
        'house_number',
        'apartment_number',
        'birthday',
        'comment',
        'sms',
        'mailing',
        'mailing_analysis',
        'black_mark',
        'black_mark_reason',
        'black_mark_comment',
        'is_skk',
        'skk_reason',
        'skk_comment',
        'is_attention',
        'attention_comment',
        'source_id',
        'is_confirmed',
        'has_registration',
        'contact_details',
        'clinics',
        'cards',
        'issued_discount_cards',
        'relatives',
        'insurance_policies',
        'legal_entity_id',
        'firstname_latin',
        'lastname_latin',
        'appointment_documents',
        'client_ids',
    ];

    /**
     * @var array
     */
    protected $with = [
        'user',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'birthday',
    ];

    /**
     * @var mixed
     */
    public $cardsToSave = null;

    /**
     * @var mixed
     */
    public $discountCardsToSave = null;

    /**
     * @var mixed
     */
    public $relativesToSave = null;

    /**
     * @var bool
     */
    public $hasChangedContacts = false;

    /**
     * @var array
     */
    protected $casts = [
        'mailing' => 'boolean',
        'sms' => 'boolean',
        'mailing_analysis' => 'boolean',
        'black_mark' => 'boolean',
        'is_skk' => 'boolean',
        'is_attention' => 'boolean',
        'is_confirmed' => 'boolean',
        'has_registration' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'contact_details'
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->cardsToSave !== null) {
                $model->saveClinicCards($model->cardsToSave);
            }
            if ($model->discountCardsToSave !== null) {
                $model->saveDiscountCards($model->discountCardsToSave);
            }
            if ($model->relativesToSave !== null) {
                $model->saveRelatives($model->relativesToSave);
            }
        });
    }

    /**
     * Related user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(Patient\User::class, 'patient_id');
    }

    /**
     * Get patient full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return collect([$this->lastname, $this->firstname, $this->middlename])->filter()->implode(' ');
    }

    /**
     * Get patient full latin name
     *
     * @return string
     */
    public function getFullNameLatinAttribute()
    {
        return collect([$this->firstname_latin, $this->lastname_latin])->filter()->implode(' ');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'patient_clinics')
                    ->orderBy('name');
    }

    /**
     * Related call requests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_requests()
    {
        return $this->hasMany(CallRequest::class);
    }

    /**
     * Related call contacts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Patient\Contact::class);
    }

    /**
     * Related information source
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(Patient\InformationSource::class, 'source_id');
    }

    /**
     * Related cache_validity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cache_validity()
    {
        return $this->hasOne(CacheValidity::class, 'patient_id');
    }

    /**
     * Fill in patient contact details
     *
     * @param array $value
     */
    public function setContactDetailsAttribute($value)
    {
        $contacts = [];

        $primaryNumber = array_get($value, 'primary_phone_number');
        if ($primaryNumber) {
            $contacts[] = $this->createOrUpdateContact($this->primary_phone, Patient\Contact::TYPE_PHONE, true, [
                'value' => $primaryNumber,
                'clinic' => array_get($value, 'primary_phone_clinic'),
                'comment' => array_get($value, 'primary_phone_comment'),
            ]);
        }

        $secondaryPhone = array_get($value, 'secondary_phone_number');
        if ($secondaryPhone) {
            $contacts[] = $this->createOrUpdateContact($this->additional_phone, Patient\Contact::TYPE_PHONE, false, [
                'value' => $secondaryPhone,
                'clinic' => array_get($value, 'secondary_phone_clinic'),
                'comment' => array_get($value, 'secondary_phone_comment'),
            ]);
        }

        $email = array_get($value, 'email');
        if ($email) {
            $contacts[] = $this->createOrUpdateContact($this->email, Patient\Contact::TYPE_EMAIL, false, [
                'value' => $email,
            ]);
        }

        $this->_cached['contact_details'] = $value;
        $this->contacts = $contacts;
    }

    /**
     * Get patient contact details
     *
     * @return array
     */
    public function getContactDetailsAttribute()
    {
        $details = [];

        if ($primaryPhone = $this->primary_phone) {
            $details['primary_phone_number'] = $primaryPhone->value;
            $details['primary_phone_clinic'] = $primaryPhone->clinic_id;
            $details['primary_phone_comment'] = $primaryPhone->comment;
        }

        if ($secondaryPhone = $this->additional_phone) {
            $details['secondary_phone_number'] = $secondaryPhone->value;
            $details['secondary_phone_clinic'] = $secondaryPhone->clinic_id;
            $details['secondary_phone_comment'] = $secondaryPhone->comment;
        }

        if ($email = $this->email) {
            $details['email'] = $email->value;
        }

        return $details;
    }

    /**
     * Get primary phone contact
     *
     * @return Patient\Contact
     */
    public function getPrimaryPhoneAttribute()
    {
        return $this->contacts->filter(function($contact) {
            return $contact->type == Patient\Contact::TYPE_PHONE && $contact->primary;
        })->first();
    }

    public function getAgeAttribute(){
        if(empty($this->birthday)){
            return null;
        }

        return Carbon::parse($this->birthday)->diffInYears(Carbon::now());
    }

    /**
     * Get additional phone contact
     *
     * @return Patient\Contact
     */
    public function getAdditionalPhoneAttribute()
    {
        return $this->contacts->filter(function($contact) {
            return $contact->type == Patient\Contact::TYPE_PHONE && !$contact->primary;
        })->first();
    }

    /**
     * Get email contact
     *
     * @return Patient\Contact
     */
    public function getEmailAttribute()
    {
        return $this->contacts->filter(function($contact) {
            return $contact->type == Patient\Contact::TYPE_EMAIL;
        })->first();
    }

    /**
     * Get loggable attribute
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getLoggableAttribute($key)
    {
        if ($key === 'email') {
            return $this->getAttributeFromArray($key);
        }
        return $this->getAttribute($key);
    }

    /**
     * Set cards to save
     *
     * @param mixed $value
     */
    public function setCardsAttribute($value)
    {
        $this->cardsToSave = $value;
    }

    /**
     * Set issued discount cards to save
     *
     * @param mixed $value
     */
    public function setIssuedDiscountCardsAttribute($value)
    {
        $this->discountCardsToSave = $value;
    }

    /**
     * Set relatives to save
     *
     * @param mixed $value
     */
    public function setRelativesAttribute($value)
    {
        $this->relativesToSave = $value;
    }

    /**
     * Create or update contact
     *
     * @param Patient\Contact|null $contact
     * @param string $type
     * @param bool $primary
     * @param array $data
     *
     * @return Patient\Contact
     */
    protected function createOrUpdateContact($contact, $type, $primary, $data)
    {
        $contact = $contact ?: new Patient\Contact([
            'type' => $type,
            'primary' => $primary,
        ]);
        $contact->fill($data);
        $this->hasChangedContacts = true;
        return $contact;
    }

    /**
     * Related treatment courses
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function treatment_courses()
    {
        return $this->hasMany(TreatmentCourse::class);
    }

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'contact_id')
            ->where('contact_type', '=', self::RELATION_TYPE);
    }

    /**
     * Related call logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_logs()
    {
        return $this->hasMany(Call\CallLog::class, 'patient_id');
    }

    /**
     * Related call logs as caller
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_logs_as_caller()
    {
        return $this->hasMany(Call\CallLog::class, 'caller_id')
            ->where('caller_type', '=', self::RELATION_TYPE);
    }

    /**
     * Related call logs as callee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_logs_as_callee()
    {
        return $this->hasMany(Call\CallLog::class, 'callee_id')
            ->where('callee_type', '=', self::RELATION_TYPE);
    }

    /**
     * Related process logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function process_logs()
    {
        return $this->hasMany(Call\ProcessLog::class, 'contact_id')
            ->where('contact_type', '=', self::RELATION_TYPE);
    }

    /**
     * Related cards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cards()
    {
        return $this->hasMany(Patient\Card::class);
    }

    /**
     * Related signal record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function signal_record()
    {
        return $this->hasOne(Patient\SignalRecord::class, 'patient_id');
    }

    /**
     * Get card number if exists
     *
     * @param int $clinicId
     * @param int $specializationId
     *
     * @return string
     */
    public function getCardNumber($clinicId, $specializationId)
    {
        $card = $this->getClinicCard($clinicId, false);

        if ($card) {
            if ($specializationId === false) {
                return $card->number;
            }
            return $card->getNumber($specializationId);
        }

        return null;
    }

    /**
     * Get archived card number if exists
     *
     * @param int $clinicId
     * @param int $specializationId
     *
     * @return string
     */
    public function getArchivedCardNumber($clinicId, $specializationId)
    {
        $card = $this->getClinicCard($clinicId, false);

        if ($card) {
            return $card->getArchivedNumber($specializationId);
        }

        return null;
    }

    /**
     * Get clinic card
     *
     * @param int $clinicId
     * @param bool $createIfNotExists
     *
     * @return Patient\Card
     */
    public function getClinicCard($clinicId, $createIfNotExists = true)
    {
        foreach ($this->cards as $card) {
            return $card;
        }

        if ($createIfNotExists) {
            return $this->createClinicCard($clinicId);
        }
        return null;
    }

    /**
     * Get patient contact which is related to the given number
     *
     * @param string $number
     *
     * @return Clinic
     */
    public function getClinicOfContact($number)
    {
        foreach ($this->contacts as $contact) {
            if ($contact->type == Patient\Contact::TYPE_PHONE && $contact->value == $number) {
                return $contact->clinic;
            }
        }
        return null;
    }

    /**
     * Create new patient clinic card
     *
     * @param int $clinicId
     *
     * @return Patient\Card
     */
    public function createClinicCard($clinicId)
    {
        $card = new Patient\Card([
            'clinic' => $clinicId,
        ]);

        $attempts = 3;
        while (true) {
            try {
                $this->cards()->save($card);
                return $card;
            } catch (Exception $e) {
                if (--$attempts <= 0 || !$this->isUniqueKeyError($e)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Check if exception was raised due to unique key duplication
     *
     * @param Exception $e
     *
     * @return bool
     */
    protected function isUniqueKeyError($e)
    {
        return strpos(
            $e->getMessage(),
            'Integrity constraint violation: 1062 Duplicate entry'
        ) !== false;
    }

    /**
     * Save related clinic cards
     *
     * @param array $cards
     */
    public function saveClinicCards($cards = [])
    {
        if (empty($cards)) {
            return;
        }

        $clinicCard = $this->getClinicCard(Arr::get($cards, '0.clinic_id'));
        $specializations = collect([]);

        foreach ($cards as $card) {
            $specializations = $specializations->concat(Arr::get($card, 'specializations', []));
        }

        $clinicCard->saveSpecializations($specializations->all());
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get visited appointments
     *
     * @return HasMany
     */
    public function visited_appointments()
    {
        return $this->appointments()
            ->whereIn('appointment_status_id', function($query) {
                $query->select('appointment_statuses.id')
                    ->from('appointment_statuses')
                    ->whereNotIn('system_status', [
                        Appointment::STATUS_SIGNED_UP,
                        Appointment::STATUS_ON_RECEPTION,
                        Appointment::STATUS_DELETED,
                        Appointment::STATUS_DIDNT_COME,
                    ])
                    ->orWhereNull('system_status');
            });
    }

    /**
     * Get visited appointments
     *
     * @return Appointment
     */
    public function getFirstAppointmentAttribute()
    {
        return $this->visited_appointments()->orderBy('date')->first();
    }

    /**
     * Related issued discount cards
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function issued_discount_cards()
    {
        return $this->belongsToMany(Patient\IssuedDiscountCard::class, 'patient_issued_cards', 'patient_id', 'issued_card_id')
            ->withPivot('disabled', 'is_owner');
    }

    /**
     * Save related discount cards
     *
     * @param array $cards
     */
    public function saveDiscountCards($cards = [])
    {
        $cardList = [];
        $disableOwnerCards = [];
        $disableTransferCards = [];

        if (!empty($cards)) {
            foreach ($cards as $card) {
                $issuedCard = $this->getDiscountCard($card);
                $patient = $this->getDiscountCardPatient($card['patients']);
                $cardList[$issuedCard->id] = [
                    'disabled' => Arr::get($patient, 'disabled', 0),
                    'is_owner' => ($patient === null || empty($patient['patient_id']))
                                    ? true
                                    : Arr::get($patient, 'is_owner'),
                ];

                if ($cardList[$issuedCard->id]['is_owner']) {
                    // Check changed disabled value
                    if ($issuedCard->owner && (bool)$issuedCard->owner->getOriginal()['pivot_disabled'] != $cardList[$issuedCard->id]['disabled']) {
                        $disableTransferCards[] = (object) [
                            'card' => $issuedCard, 'disabled' => $cardList[$issuedCard->id]['disabled']
                        ];
                    };
                }

                if (isset($card['disableOwner']) && $card['disableOwner'] == true) {
                    $disableOwnerCards[] = $issuedCard;
                }
            }
        }

        $this->issued_discount_cards()->sync($cardList);
        $this->changeDisableStatusToTransferCards($disableTransferCards);
        $this->disableOwnerCards($disableOwnerCards);
        $this->load('issued_discount_cards');
    }

    /**
     * Get patient discount card data
     *
     * @param array $patients
     *
     * @return array
     */
    protected function getDiscountCardPatient($patients)
    {
        return Arr::first($patients, function ($item) {
            return $item['patient_id'] == $this->id;
        });
    }

    /**
     * Get clinic card
     *
     * @param array $cardData
     *
     * @return Patient\IssuedDiscountCard
     */
    public function getDiscountCard($cardData = [])
    {
        $id = Arr::get($cardData, 'id');

        if (isset($id)) {
            $card = Patient\IssuedDiscountCard::find($id);
        } else {
            $card = new Patient\IssuedDiscountCard();
        }

        return $this->persistDiscountCard($card, $cardData);
    }

    /**
     * Create new patient discount card
     *
     * @param array $data
     *
     * @return Patient\IssuedDiscountCard
     */
    public function persistDiscountCard($card, $data = [])
    {
        $card->discount_card_type_id = Arr::get($data ,'discount_card_type_id');
        $card->clinic_id = Arr::get($data, 'clinic_id');
        $card->issued = Arr::get($data, 'issued');
        $card->valid_from = Arr::get($data, 'valid_from');
        $card->expires = Arr::get($data, 'expires');
        $card->comment = Arr::get($data, 'comment');
        $card->save();
        return $card;
    }

    /**
     * Disable discount card for its owner
     *
     * @param array $cards
     */
    protected function disableOwnerCards($cards = [])
    {
        if (empty($cards)) {
            return;
        }

        foreach($cards as $card) {
            $card->owner->issued_discount_cards()
                 ->updateExistingPivot($card->id, ['disabled' => true]);
        }
    }

    /**
     * Disable discount card for transfer
     *
     * @param array $cards
     */
    protected function changeDisableStatusToTransferCards($cardsWithStatus = [])
    {
        if (empty($cardsWithStatus)) {
            return;
        }

        foreach($cardsWithStatus as $cardWithStatus) {
            if (!$cardWithStatus->disabled) {
                $max_owners = $cardWithStatus->card->discount_card_type()->first()->max_owners;
                if ($max_owners < count($cardWithStatus->card->patients)) {
                    continue;
                }
            }
            foreach ($cardWithStatus->card->patients as $patient) {
                $patient->issued_discount_cards()
                    ->updateExistingPivot($cardWithStatus->card->id, ['disabled' => $cardWithStatus->disabled]);
            };
        }
    }

    /**
     * Related card records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function card_records()
    {
        return $this->hasManyThrough(Patient\Card\Record::class, Appointment::class, 'patient_id', 'appointment_id');
    }

    /**
     * Related analysis_results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_results()
    {
        return $this->hasMany(Analysis\Result::class);
    }

    /**
     * Get patient assigned analysis
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_analyses()
    {
        return $this->analysis_results()
            ->where('is_archived', 0)
            ->where('status', '=', Analysis\Result::STATUS_ASSIGNED)
            ->whereDoesntHave('appointment_service_item');
    }

    /**
     * Get patient assigned services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Patient\AssignedService::class);
    }

    /**
     * Get patient assigned services where has quantity greater than 0
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_services()
    {
        return $this->services()
            ->where('is_archived', 0)
            ->where('quantity', '>', 0);
    }

    /**
     * Get patient assigned medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_medicines()
    {
        return $this->hasMany(Patient\AssignedMedicine::class, 'patient_id');
    }

    /**
     * Related assigned consultations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function assigned_consultations()
    {
        return $this->card_records()
            ->where('recordable_type', '=', Patient\Card\ConsultationRecord::RELATION_TYPE);
    }

    /**
     * Get patient appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_services()
    {
        return $this->hasMany(AppointmentService::class);
    }

    /**
     * Get patient appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medicine_services()
    {
        return $this->appointment_services()->where('container_type', '=', AppointmentService::CONTAINER_MEDICINES);
    }

    /**
     * Related patient relatives
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function relatives()
    {
        return $this->belongsToMany(Patient::class, 'patient_relatives', 'patient_id', 'relative_id')
            ->using(Patient\Relative::class)
            ->withPivot(
                'relation',
                'show_in_cabinet',
                'show_in_cabinet_after_14',
                'cabinet_deny_reason',
                'proving_document_id'
            );
    }

    /**
     * Related patient relative links
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relative_links()
    {
        return $this->hasMany(Patient\Relative::class, 'patient_id');
    }

    /**
     * Related patient accounts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Patient\Account::class, 'patient_id');
    }

    /**
     * Related uploaded documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function uploaded_documents()
    {
        return $this->hasMany(Patient\Card\Document::class, 'patient_id');
    }

    /**
     * Related uploaded documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filled_questionnaires()
    {
        return $this->uploaded_documents()->where('is_questionnaire', 1);
    }

    /**
     * Related patient payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'patient_id');
    }

    /**
     * Related site enquiries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function site_enquiries()
    {
        return $this->hasMany(SiteEnquiry::class, 'patient_id');
    }

    /**
     * Related insurance policies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insurance_policies()
    {
        return $this->hasMany(Patient\InsurancePolicy::class, 'patient_id');
    }

    /**
     * Get account by clinic_id
     *
     * @return mixed
     */
    public function getClinicAccount($clinic_id)
    {
        return $this->accounts()->where('clinic_id', '=', $clinic_id)->first();
    }

    /**
     * Save relatives
     *
     * @param array $data
     */
    public function saveRelatives(array $data)
    {
        $this->relatives()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['id'],
                'data' => Arr::only($item, [
                    'relation',
                    'show_in_cabinet',
                    'show_in_cabinet_after_14',
                    'cabinet_deny_reason',
                    'proving_document_id',
                ]),
            ];
        }, $data), 'data', 'id'));

        $this->updateRelatives();
    }

    /**
     * Manage updating relatives relations
     */
    public function updateRelatives()
    {
        $relatives = $this->relatives()->get();

        if ($relatives->isEmpty()) {
            return;
        }

        $this->updatePatientRelativeRelations($relatives);
        $this->addOpositeRelations($relatives, $this->gender);
        $this->load('relatives');
    }

    /**
     * Update relative relashions
     *
     * @param collection $relatives
     */
    public function updatePatientRelativeRelations($relatives)
    {
        $relatives->each(function($patient) use ($relatives) {
            $filtered = $this->filterPatientRelations($patient, $relatives)->toArray();

            if (empty($filtered)) {
                return;
            }

            $relations = $this->getPatientRelationData($patient->pivot->relation, $filtered);
            $patient->relatives()->syncWithoutDetaching($relations);
        });
    }

    /**
     * Get relative model data
     *
     * @param string $parentRelation
     * @param array  $relatives
     *
     * @return array
     */
    public function getPatientRelationData($parentRelation, $relatives)
    {
        $results = [];

        foreach ($relatives as $item) {
            $relation = PatientRelative::getDeepOpositeRelation($item['pivot']['relation'], $parentRelation, $item['gender']);

            if ($relation) {
                $results[$item['id']] = [
                    'relation' => $relation,
                ];
            }
        }
        return $results;
    }

    /**
     * Get patient not binded between each other relatives
     *
     * @param Patient @patient
     * @param collection relatives
     *
     * @return collection
     */
    public function filterPatientRelations($patient, $relatives)
    {
        return $relatives->filter(function($item) use ($patient){
            return $patient->id !== $item->pivot->relative_id;
        });
    }

    /**
     * Save oposite relations
     *
     * @param collection relatives
     * @param string $gender
     */
    public function addOpositeRelations($relatives, $gender)
    {
        if (empty($gender)) {
            return;
        }

        $relatives->each(function($relative) use ($gender) {
            $oposite = PatientRelative::getOpositeRelation($relative->pivot->relation, $gender);

            if ($oposite == null) {
                return;
            }

            $relative->relatives()->syncWithoutDetaching([
                $this->id => ['relation' => $oposite],
            ]);
        });
    }

    /**
     * Get patient previous visit by clinic and specialization
     *
     * @param int clinic
     * @param int specialization
     *
     * @return mixed
     */
    public function getPrevSpecializationAppointment($clinic, $specialization)
    {
        return $this->visited_appointments()
            ->where('clinic_id', '=', $clinic)
            ->where('specialization_id', '=', $specialization)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Get patient previous visit to employee by clinic, card specialization and date
     *
     * @param int $clinic
     * @param int $specialization
     * @param string $date
     *
     * @return mixed
     */
    public function getEmployeePrevSpecializationCardAppointment($clinic, $specialization, $date, $start)
    {
        return $this->visited_appointments()
            ->where('clinic_id', '=', $clinic)
            ->where('card_specialization_id', '=', $specialization)
            ->where('date', '<=', $date)
            ->where('end', '<=', $start)
            ->where('doctor_type', '=', Employee::RELATION_TYPE)
            ->orderBy('date', 'desc')
            ->orderBy('start', 'desc')
            ->first();
    }

    /**
     * Get debt attribute
     *
     * @return int
     */
    public function getServiceDebtAttribute()
    {
        $debtServices = $this->getDebtServices();

        if ($debtServices->isEmpty()) {
            return 0;
        }
        return $debtServices->reduce(function($sum, $item) {
            return $sum + ($item->cost - $item->paid);
        }, 0);
    }

    /**
     * Get debt attribute
     *
     * @return int
     */
    public function getCardServiceDebt($card_spec_id)
    {
        $appointmentIds = $this->appointments()
            ->select('id')
            ->where('card_specialization_id', '=', $card_spec_id)->pluck('id');

        $debtServices = $this->getCardDebtServices($appointmentIds);

        if ($debtServices->isEmpty()) {
            return 0;
        }
        return $debtServices->reduce(function($sum, $item) {
            return $sum + ($item->cost - $item->paid);
        }, 0);
    }

    /**
     * Get patient not full payed appointment services
     *
     * @return mixed
     */
    protected function getCardDebtServices($appointmentIDs)
    {
        return $this->getServicesBalance(function($query) use ($appointmentIDs){
            $query->havingRaw('`paid` < `appointment_services`.`cost` OR `paid` IS NULL')
            ->whereIn('appointment_services.appointment_id', $appointmentIDs);
        });
    }

    /**
     * Get patient not full payed appointment services
     *
     * @return mixed
     */
    protected function getDebtServices()
    {
        return $this->getServicesBalance(function($query) {
            $query->havingRaw('`paid` < `appointment_services`.`cost` OR `paid` IS NULL');
        });
    }

    /**
     * Get patient appointment services
     *
     * @param $callback
     *
     * @return mixed
     */
    protected function getServicesBalance($callback = false)
    {
        $serviceRepository = app(AppointmentServiceRepository::class);
        return $serviceRepository->getPatientBalance($this->id, $callback);
    }

    /**
     * Create/associate user account
     *
     * @param Patient\Registration $registration
     *
     * @return bool
     */
    public function createUser($registration = null)
    {
        if ($registration !== null) {
            if ($registration->status !== Patient\Registration::STATUS_REGISTERED) {
                $user = $registration->user;
                if ($user !== null && $user->patient_id === null) {
                    $user->patient()->associate($this);
                    $user->save();
                    $registration->status = Patient\Registration::STATUS_REGISTERED;
                    $registration->save();
                    return true;
                }
            }
        } else {
            $phone = $this->primary_phone->value;
            $user = Patient\User::where('phone', $phone)->first();
            if ($user !== null) {
                if ($user->patient_id === null) {
                    $user->patient()->associate($this);
                    $user->save();
                    return true;
                }
            } else {
                $password = Str::random(6);
                $user = $this->user()->create([
                    'phone' => $phone,
                    'password' => $password,
                ]);
                $user->sendPassword($password);
                return true;
            }
        }
        return false;
    }

    /**
     * Related legal entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legal_entity()
    {
        return $this->belongsTo(LegalEntity::class, 'legal_entity_id');
    }

    /**
     * Related service prepayments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function service_prepayments()
    {
        return $this->hasMany(Patient\Prepayment::class, 'patient_id');
    }

    /**
     * Related insurance act services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insurance_act_services()
    {
        return $this->hasMany(Service::class, 'patient_id');
    }

    /**
     * Related waitlist records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function waitlist_records()
    {
        return $this->hasMany(WaitListRecord::class, 'patient_id');
    }


    /*
     * Related appointment document
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_documents()
    {
        return $this->hasMany(AppointmentDocument::class, 'patient_id', 'id');

    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ehealth_patient()
    {
        return $this->hasOne(\App\V1\Models\Ehealth\Patient::class, 'patient_id');
    }

    /**
     * Related clientIDs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function client_ids()
    {
        return $this->hasMany(ClientIds::class, 'patient_id', 'id');
    }
}
