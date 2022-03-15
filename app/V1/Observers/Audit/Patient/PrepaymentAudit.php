<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\Service;
use App\V1\Models\Clinic;

class PrepaymentAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'patient_id',
        'service_id',
        'clinic_id',
        'used',
        'amount',
    ];
    
    /**
     * Format amount
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatAmountAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format used
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatUsedAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format service_id
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatServiceIdAttribute($value)
    {
        return $this->fetchAttribute(Service::class, $value, 'name');
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
     * Format clinic_id
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatClinicIdAttribute($value)
    {
        return $this->fetchAttribute(Clinic::class, $value, 'name');
    }
}