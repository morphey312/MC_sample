<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\Employee;

class InformationSourceAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'is_active',
        'is_collective_form',
        'show_in_appointment',
        'media_type',
        'employee_id',
    ];
    
    /**
     * Format is_collective_form
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsCollectiveFormAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format show_in_appointment
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatShowInAppointmentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format employee_id
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatEmployeeIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();
        
        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh);
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model) 
            + $this->getCustomAttributes($model);
    }

    /**
     * Get custom attributes from user model
     * 
     * @param \App\V1\Models\Service $model
     * 
     * @return array
     */ 
    protected function getCustomAttributes($model)
    {
        return [
            'clinics' => $model->clinics->pluck('name')->all(),
        ];
    }
}