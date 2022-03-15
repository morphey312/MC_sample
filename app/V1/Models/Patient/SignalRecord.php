<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class SignalRecord extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const RELATION_TYPE = 'signal_record';
    const N_A = 'n_a';

    /**
     * @var string
     */
    protected $table = 'patient_signal_records';

    /**
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'blood_group',
        'rhesus_factor',
        'diabetes',
        'transfusion',
        'transfusion_comment',
        'drug_intolerance',
        'infections',
        'surgical_interventions',
        'allergic_history',
        'patient_feedback',
        'onco_observation_gyn',
        'onco_observation_gyn_date',
        'onco_observation_pro',
        'onco_observation_pro_date',
        'onco_observation_uro',
        'onco_observation_uro_date',
        'onco_observation_ren',
        'onco_observation_ren_date',
        'onco_observation_vil',
        'onco_observation_vil_date',
        'onco_observation_vaserman',
        'onco_observation_vaserman_date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'transfusion' => 'boolean',
    ];

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
