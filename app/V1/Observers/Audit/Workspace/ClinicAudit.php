<?php

namespace App\V1\Observers\Audit\Workspace;

use App\V1\Models\Clinic;
use App\V1\Models\Specialization;
use App\V1\Models\Workspace;
use App\V1\Observers\Audit\BaseRelationAudit;
use Illuminate\Support\Arr;

class ClinicAudit extends BaseRelationAudit
{
    /**
     * @var string
     */
    protected $foreignKey = 'workspace_id';

    /**
     * @var string
     */
    protected $relatedType = Workspace::RELATION_TYPE;

    /**
     * @var array
     */
    protected $attributes = [
        'sip_number',
        'appointment_duration',
        'specializations',
    ];

    /**
     * Format appointment_duration
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatAppointmentDurationAttribute($value)
    {
        return (int) $value;
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + ['old_clinic' => $fresh->clinic->name];
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {

        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model)
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

    /**
     * Get custom attributes from clinic model
     *
     * @param \App\V1\Models\Workspace\Clinic $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [];

        if ($model->specializationsToSave !== null) {
            $data['specializations'] = $this->fetchAttribute(Specialization::class, $model->specializationsToSave, 'name');
        } else {
            $data['specializations'] = $model->specializations->pluck('name')->all();
        }

        return $data;
    }
}
