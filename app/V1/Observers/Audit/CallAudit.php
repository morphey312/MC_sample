<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Patient;
use App\V1\Models\Specialization;
use App\V1\Models\Employee;
use App\V1\Models\Call\Result;
use App\V1\Models\Clinic;
use App\V1\Models\CallRequest;
use App\V1\Models\Call\DeleteReason;

class CallAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'comment',
        'time',
        'date',
        'is_first',
        'call_result_id',
        'clinic_id',
        'employee_id',
        'specialization_id',
        'call_request_id',
        'call_delete_reason_id',
        'delete_reason_comment',
    ];
    
    /**
     * Format time 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatTimeAttribute($value)
    {
        return $this->formatTime($value);
    }
    
    /**
     * Format date 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatDateAttribute($value)
    {
        return $this->formatDate($value);
    }
    
    /**
     * Format is_first 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsFirstAttribute($value)
    {
        return (bool) $value;
    }
    
    /**
     * Format call_result_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCallResultIdAttribute($value)
    {
        return $this->fetchAttribute(Result::class, $value, 'name');
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
    
    /**
     * Format call_request_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCallRequestIdAttribute($value)
    {
        return $this->fetchAttribute(CallRequest::class, $value, 'name');
    }
    
    /**
     * Format call_delete_reason_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCallDeleteReasonIdAttribute($value)
    {
        return $this->fetchAttribute(DeleteReason::class, $value, 'name');
    }
    
    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($model->getOriginal());
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model) 
            + $this->getCustomAttributes($model->getAttributes());
    }
    
    /**
     * Get custom attributes from user model
     * 
     * @param array $data
     * 
     * @return array
     */ 
    protected function getCustomAttributes($attributes)
    {
        $data = [];
        
        if (isset($attributes['contact_type']) && isset($attributes['contact_id'])) {
            if ($attributes['contact_type'] === Patient::RELATION_TYPE) {
                $data['contact'] = $this->formatPatientName($attributes['contact_id']);
            } elseif ($attributes['contact_type'] === Employee::RELATION_TYPE) {
                $data['contact'] = $this->fetchAttribute(Employee::class, $attributes['contact_id'], 'full_name');
            }
        }
        
        return $data;
    }
}