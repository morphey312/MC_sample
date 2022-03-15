<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Specialization;
use App\V1\Models\Employee;
use App\V1\Models\Patient;

class TreatmentCourseAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'start',
        'end',
        'patient_id',
        'doctor_id',
        'card_specialization_id',
    ];
    
    /**
     * Format start 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatStartAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format end 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatEndAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format patient_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatPatientIdAttribute($value)
    {
        return $this->formatPatientName($value);
    }

    /**
     * Format doctor_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatDoctorIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format card_specialization_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCardSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }
}