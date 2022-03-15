<?php

namespace App\V1\Observers\Audit\Clinic\Specialization;

use App\V1\Models\Clinic\MoneyReciever;
use App\V1\Models\Specialization;
use App\V1\Observers\Audit\BaseRelationAudit;

class ClinicAudit extends BaseRelationAudit
{
    /**
     * @var string
     */
    protected $foreignKey = 'specialization_id';

    /**
     * @var string
     */
    protected $relatedType = Specialization::RELATION_TYPE;

    /**
     * @var array
     */
    protected $attributes = [
        'show_days_since_message',
        'first_patient_appointment_limit',
        'days_since_last_visit',
        'money_reciever_id',
        'status',
    ];

    /**
     * Format show_days_since_message
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatShowDaysSinceMessageAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format first_patient_appointment_limit
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function formatFirstPatientAppointmentLimitAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Format days_since_last_visit
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function formatDaysSinceLastVisitAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Format money_reciever_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatMoneyRecieverIdAttribute($value)
    {
        return $this->fetchAttribute(MoneyReciever::class, $value, 'name');
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + ['old_clinic' => $fresh->clinic->name];
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + ['new_clinic' => $model->clinic->name];
    }

    /**
     * @inherit
     */
    protected function getChangedAttributes($new, $old)
    {
        $changed = parent::getChangedAttributes($new, $old);

        if (count($changed) === 2) {
            // only old_clinic and new_clinic
            if ($new['new_clinic'] == $old['old_clinic']) {
                return [];
            }
        }

        return $changed;
    }
}
