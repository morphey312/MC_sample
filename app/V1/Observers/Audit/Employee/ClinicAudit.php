<?php

namespace App\V1\Observers\Audit\Employee;

use App\V1\Observers\Audit\BaseRelationAudit;
use App\V1\Models\Employee\Position;
use App\V1\Models\Employee;
use App\V1\Models\Specialization;
use Illuminate\Support\Arr;

class ClinicAudit extends BaseRelationAudit
{
    /**
     * @var string
     */ 
    protected $foreignKey = 'employee_id';
    
    /**
     * @var string
     */
    protected $relatedType = Employee::RELATION_TYPE;
    
    /**
     * @var array
     */ 
    protected $attributes = [
        'status',
        'position_id',
        'is_primary',
        'can_recieve_transfer',
        'sip_number',
        'date_start_working',
        'date_end_working',
    ];
    
    /**
     * Format is_primary 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsPrimaryAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format can_recieve_transfer 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatCanRecieveTransferAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format date_start_working
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateStartWorkingAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format date_end_working
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateEndWorkingAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format position_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatPositionIdAttribute($value)
    {
        return $this->fetchAttribute(Position::class, $value, 'name');
    }
    
    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();
        
        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + ['old_clinic' => $fresh->clinic->name]
            + ($model->isDirty('sip_password') ? ['sip_password' => 'old'] : []);
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model) 
            + $this->getCustomAttributes($model)
            + ['new_clinic' => $model->clinic->name]
            + ($model->isDirty('sip_password') ? ['sip_password' => 'new'] : []);
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
     * Get custom attributes from user model
     * 
     * @param \App\V1\Models\User $model
     * 
     * @return array
     */ 
    protected function getCustomAttributes($model)
    {
        $data = [];
        
        if ($model->hasSpecializations()) {
            if ($model->specializationsToSave !== null) {
                $specializationIds = Arr::pluck($model->specializationsToSave, 'specialization_id');
                $data['specializations'] = $this->fetchAttribute(Specialization::class, $specializationIds, 'name');
            } else {
                $data['specializations'] = $model->specializations->pluck('name')->all();
            }
        }
        
        if ($model->isDoctor()) {
            $data['appointment_duration'] = object_get($model, 'doctor.appointment_duration');
            $data['appointment_duration_repeated'] = object_get($model, 'doctor.appointment_duration_repeated');
        }
        
        return $data;
    }
}