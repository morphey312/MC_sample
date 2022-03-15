<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Employee\Doctor;

class DiagnosticReport extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'ehealth_diagnostic_reports';

    /**
     * @var array
     */
    protected $fillable = [
        'encounter_id',
        'issued',
        'category',
        'recorded_by',
        'conclusion',
        'division',
        'conclusion_code',
        'code',
        'effective_period_start',
        'effective_period_end',
        'results_interpreter',
        'performer',
        'primary_source',
        'paper_referral',
        'paper_referral',
        'paper_referral_requester_employee_name',
        'paper_referral_requisition',
        'paper_referral_service_request_date',
        'paper_referral_note',
        'paper_referral_requester_legal_entity_name',
        'paper_referral_requester_legal_entity_edrpou',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'conclusion_code' => 'array',
        'primary_source' => 'boolean',
        'paper_referral' => 'boolean',
    ];

    /**
     * Related encounter
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
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resultsInterpreter()
    {
        return $this->belongsTo(Doctor::class, 'results_interpreter');
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
