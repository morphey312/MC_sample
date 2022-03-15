<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient\InformationSource;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class Clinic extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    const RELATION_TYPE = 'clinic';
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var array
     */
    protected $fillable = [
        'msp_id',
        'currency',
        'country_id',
        'city',
        'name',
        'signer',
        'signer_position',
        'official_name',
        'official_additional',
        'contact_phone',
        'additional_contact_phone',
        'email',
        'working_hours',
        'map_link',
        'status',
        'voip_queue',
        'medicine_stores',
        'blanks',
        'image_id',
        'group_id',
        'questionnaire_blank',
        'money_reciever_id',
        'analysis_check_url',
        'kind',
        'address',
        'reception_address',
        'lat',
        'lng',
        'authority_name',
        'not_round_cost',
        'short_name',
        'need_apply_city',
        'official_edrpou',
        'need_apply_city',
        'export_patients_contacts',
        'medicine_firm_id',
        'is_default',
        'works_with_apteka24',
        'show_on_site',
        'secure_analysis'
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'call_requests',
        'appointments',
        'employees',
        'personal_tasks',
        'call_process_logs',
        'call_logs',
        'calls',
        'prices',
        'appointment_limitations',
        'specializations',
        'analysis_results',
        'patient_cards',
        'services',
        'patients',
        'analyses',
        'operator_bonus',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'sent_to_ehealth_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'address' => 'object',
        'reception_address' => 'object',
        'working_hours' => 'object',
        'active_in_ehealth' => 'boolean',
        'not_round_cost' => 'boolean',
        'need_apply_city' => 'boolean',
        'is_default' => 'boolean',
        'works_with_apteka24' => 'boolean',
        'show_on_site' => 'boolean',
        'secure_analysis' => 'boolean',
    ];

    /**
     * Related msp
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function msp()
    {
        return $this->belongsTo(Msp::class, 'msp_id');
    }

    /**
     * Related employees
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_clinics', 'clinic_id', 'employee_id');
    }

    /**
     * Related workspaces
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_clinics', 'clinic_id', 'workspace_id');
    }

    /**
     * Related call requests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_requests()
    {
        return $this->hasMany(CallRequest::class, 'clinic_id');
    }

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'clinic_id');
    }

    /**
     * Related specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'clinic_specialization', 'clinic_id', 'specialization_id');
    }

    /**
     * Related specialization clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clinic_specializations()
    {
        return $this->hasMany(Specialization\Clinic::class, 'clinic_id');
    }

    /**
     * Related patients
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_clinics', 'clinic_id', 'patient_id');
    }

    /**
     * Related patient cards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_cards()
    {
        return $this->hasMany(Patient\Card::class, 'clinic_id');
    }

    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function prices()
    {
        return $this->belongsToMany(Price::class, 'price_clinics', 'clinic_id', 'price_id');
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_clinics', 'clinic_id', 'service_id');
    }

    /**
     * Related analyses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function analyses()
    {
        return $this->belongsToMany(Analysis::class, 'analysis_clinics', 'clinic_id', 'analysis_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id');
    }

    /**
     * Related personal tasks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personal_tasks()
    {
        return $this->hasMany(PersonalTask::class, 'clinic_id');
    }

    /**
     * Related call process logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_process_logs()
    {
        return $this->hasMany(Call\ProcessLog::class, 'clinic_id');
    }

    /**
     * Related call logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_logs()
    {
        return $this->hasMany(Call\CallLog::class, 'clinic_id');
    }

    /**
     * Related appointment limitations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_limitations()
    {
        return $this->hasMany(Appointment\Limitation::class, 'clinic_id');
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_results()
    {
        return $this->hasMany(Analysis\Result::class, 'clinic_id');
    }

    /**
     * Related medicine stores
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicine_stores()
    {
        return $this->belongsToMany(MedicineStore::class, 'clinic_medicine_store', 'clinic_id', 'medicine_store_id');
    }

    /**
     * Related payment methods
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payment_methods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'clinic_payment_method', 'clinic_id', 'payment_method_id')
                    ->withPivot('is_fiscal');
    }

    /**
     * Related payment methods
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function act_price_clinics()
    {
        return $this->belongsToMany(PaymentMethod::class, 'price_agreement_act_price_clinics', 'clinic_id', 'act_price_id')
                    ->withPivot('code', 'duration_days');
    }

    /**
     * Related non fiscal payment methods
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function non_fiscal_payment_methods()
    {
        return $this->payment_methods()->wherePivot('is_fiscal', 0);
    }

    /**
     * Related non online payment methods
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function non_online_payment_methods()
    {
        return $this->payment_methods()->where('online_payment', '=', 0);
    }

    /**
     * Related discount card types
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function discount_card_types()
    {
        return $this->belongsToMany(DiscountCardType::class, 'clinic_discount_card', 'clinic_id', 'discount_card_type_id')
                    ->orderBy('name')
                    ->withPivot('payment_method_id');
    }

    /**
     * Related blanks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blanks()
    {
        return $this->hasMany(Clinic\Blank::class, 'clinic_id');
    }

    /**
     * Related cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cashboxes()
    {
        return $this->hasMany(Employee\Cashbox::class, 'clinic_id');
    }

    /**
    * Related insurance companies
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function insurance_companies()
    {
        return $this->belongsToMany(InsuranceCompany::class, 'insurance_company_clinics', 'clinic_id', 'insurance_company_id');
    }

    /**
     * Related country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Related image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo(FileAttachment::class, 'image_id');
    }

    /**
     * Related group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Clinic\Group::class, 'group_id');
    }

    /**
     * Related questionnaire file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function questionnaire()
    {
        return $this->belongsTo(FileAttachment::class, 'questionnaire_blank');
    }

    /**
     * Related money recievers
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function money_reciever()
    {
        return $this->belongsTo(Clinic\MoneyReciever::class, 'money_reciever_id');
    }

    /**
     * Related operator bonuses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function operator_bonus()
    {
        return $this->belongsToMany(Employee\OperatorBonus::class, 'clinic_operator_bonus', 'clinic_id', 'operator_bonus_id');
    }

    /**
     * Related service types
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function service_types()
    {
        return $this->hasMany(Clinic\ServiceType::class, 'clinic_id');
    }

    /**
     * Related information source
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function information_source()
    {
        return $this->belongsToMany(InformationSource::class, 'information_source_clinics', 'clinic_id', 'source_id');
    }

    /**
     * Related medicine firm
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicine_firm()
    {
        return $this->belongsTo(MedicineFirm::class, 'medicine_firm_id');
    }
}
