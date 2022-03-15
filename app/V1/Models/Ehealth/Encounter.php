<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\Appointment;
use App\V1\Models\BaseModel;
use Auth;

class Encounter extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'ehealth_encounters';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'priority',
        'appointment_id',
        'care_episode_id',
        'reasons',
        'date',
        'action_references',
        'prescriptions',
        'hospitalization',
        'hospitalization_admit_source',
        'hospitalization_re_admission',
        'hospitalization_discharge_disposition',
        'hospitalization_pre_admission_identifier',
        'incoming_referral',
        'paper_referral_requester_legal_entity_edrpou',
        'paper_referral',
        'paper_referral_requester_employee_name',
        'paper_referral_requester_legal_entity_name',
        'paper_referral_requisition',
        'paper_referral_service_request_date',
        'paper_referral_note',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'reasons' => 'array',
    ];

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related care episodes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function care_episode()
    {
        return $this->belongsTo(CareEpisode::class, 'care_episode_id');
    }

    /**
     * Related employee documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conditions()
    {
        return $this->hasMany(Condition::class, 'encounter_id');
    }

    /**
     * Related employee documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function diagnosticReports()
    {
        return $this->hasMany(DiagnosticReport::class, 'encounter_id');
    }

    /**
     * Related employee documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'encounter_id');
    }
}
