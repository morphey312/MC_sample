<?php

namespace App\V1\Observers\Audit\Employee;

use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\Employee;
use App\V1\Models\Clinic;
use App\V1\Models\Specialization;

class DoctorIncomePlanAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'employee_id',
        'clinic_id',
        'specialization_id',
        'plan_service_mark',
        'year',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
    ];
    
    /**
     * Format employee_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatEmployeeIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format clinic_id
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatClinicIdAttribute($value)
    {
        return $this->fetchAttribute(Clinic::class, $value, 'name');
    }

    /**
     * Format specialization_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }
}