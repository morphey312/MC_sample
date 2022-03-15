<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\Ehealth\Patient\Authentication;
use App\V1\Models\Ehealth\Patient\ConfidantPerson;
use App\V1\Models\Ehealth\Patient\Document;
use App\V1\Models\Employee;
use Auth;

class Patient extends BaseModel
{
    const STATUS_PATIENT = 'patient';
    const STATUS_GUEST = 'guest';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    const EHEALTH_STATUS_SIGNED = 'SIGNED';

    const RESIDENCE = 'RESIDENCE';

    const RELATION_TYPE = 'ehealth_patient';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'ehealth_patients';

    /**
     * @var array
     */
    protected $fillable = [
        'first_name',
        'patient_id',
        'patient_type',
        'last_name',
        'second_name',
        'birth_country',
        'birth_settlement',
        'birth_date',
        'gender',
        'phone_number',
        'phone_type',
        'email',
        'preferred_way_communication',
        'secret',
        'no_tax_id',
        'tax_id',
        'unzr',
        'address',
        'trusted_person_last_name',
        'trusted_person_first_name',
        'trusted_person_second_name',
        'trusted_person_phone_type',
        'trusted_person_phone_number',
        'incompetent',
        'patient_signed',
        'process_disclosure_data_consent',
        'patient_documents',
        'patient_authentications',
        'confidant_person',
    ];


    /**
     * @var array
     */
    protected $dates = [
        'birthday',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'patient_documents',
        'patient_authentications',
        'confidant_person'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'address' => 'object',
        'urgent' => 'object',
        'no_tax_id' => 'boolean',
        'patient_signed' => 'boolean',
        'process_disclosure_data_consent' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->employee = Auth::user()->getEmployeeModel();
        });
    }

    /**
     * Related patient documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_documents()
    {
        return $this->morphMany(Document::class, 'owner');
    }

    /**
     * Related patient authentications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_authentications()
    {
        return $this->hasMany(Authentication::class, 'ehealth_patient_id');
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(\App\V1\Models\Patient::class, 'patient_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Related patient authentications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function confidant_person()
    {
        return $this->hasOne(ConfidantPerson::class, 'ehealth_patient_id');
    }
}
