<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Clinic;
use App\V1\Models\Analysis\Laboratory;
use Illuminate\Support\Arr;

class AnalysisAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'name',
        'laboratory_code',
        'laboratory_id',
        'disabled',
    ];
    
    /**
     * Format laboratory_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatLaboratoryIdAttribute($value)
    {
        return $this->fetchAttribute(Laboratory::class, $value, 'name');
    }
    
    /**
     * Format disabled
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatDisabledAttribute($value)
    {
        return (bool) $value;
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
        $data = [];
        
        if ($model->clinicsToSave !== null) {
            $clinicIds = Arr::pluck($model->clinicsToSave, 'clinic_id');
            $data['clinics'] = $this->fetchAttribute(Clinic::class, $clinicIds, 'name');
        } else {
            $data['clinics'] = $model->clinics->pluck('name')->all();
        }
        
        return $data;
    }
}