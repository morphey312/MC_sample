<?php

namespace App\V1\Observers\Audit;

class EmployeeAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'phone',
        'last_name',
        'first_name',
        'middle_name',
        'is_translator',
    ];
    
    /**
     * Format is_translator 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsTranslatorAttribute($value)
    {
        return (bool) $value;
    }
}