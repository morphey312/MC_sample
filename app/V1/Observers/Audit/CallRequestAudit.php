<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Patient;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic;
use App\V1\Models\CallRequest\Purpose;

class CallRequestAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'recall_from',
        'recall_to',
        'status',
        'comment',
        'comment_canceled',
        'call_request_purpose_id',
        'patient_id',
        'clinic_id',
        'specialization_id',
    ];
    
    /**
     * Format recall_from 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatRecallFromAttribute($value)
    {
        return $this->formatDate($value);
    }
    
    /**
     * Format recall_to 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatRecallToAttribute($value)
    {
        return $this->formatDate($value);
    }
    
    /**
     * Format call_request_purpose_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCallRequestPurposeIdAttribute($value)
    {
        return $this->fetchAttribute(Purpose::class, $value, 'name');
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