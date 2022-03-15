<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Observers\Audit\BaseAudit;

class SignalRecordAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
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
        'onco_observation_vaserman_date'
    ];

    /**
     * Format transfusion
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatTransfusionAttribute($value)
    {
        return (bool) $value;
    }
}
