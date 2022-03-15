<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Employee\Doctor;

class Procedure extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'ehealth_procedures';

    protected $fillable = [
        'encounter_id',
        'code',
        'category',
        'division',
        'recorded_by',
        'performer',
        'outcome',
        'note',
        'performed_date_time',
        'paper_referral',
        'paper_referral_requester_employee_name',
        'paper_referral_requisition',
        'paper_referral_service_request_date',
        'paper_referral_note',
        'paper_referral_requester_legal_entity_name',
        'paper_referral_requester_legal_entity_edrpou',
        'primary_source',
        'explanatory_letter',
        'status_reason',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'primary_source' => 'boolean',
        'paper_referral' => 'boolean',
    ];

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recordedBy()
    {
        return $this->belongsTo(Doctor::class, 'recorded_by');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function performerDoctor()
    {
        return $this->belongsTo(Doctor::class, 'performer');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'division');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'code');
    }
}
