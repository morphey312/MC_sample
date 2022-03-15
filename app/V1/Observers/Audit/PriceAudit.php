<?php

namespace App\V1\Observers\Audit;

use Illuminate\Support\Arr;
use App\V1\Models\Price;
use App\V1\Models\Price\Set;

class PriceAudit extends BaseRelationAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'date_from',
        'date_to',
        'cost',
        'self_cost',
        'currency',
        'set_id',
    ];
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {
        $log->loggable_id = $model->service_id;
        $log->loggable_type = Price::RELATION_TYPE;
        $log->category = $model->service_type;
    }
    
    /**
     * Format date_from 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatDateFromAttribute($value)
    {
        return $this->formatDate($value);
    }
    
    /**
     * Format date_to 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatDateToAttribute($value)
    {
        return $this->formatDate($value);
    }
    
    /**
     * Format cost 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCostAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }
    
    /**
     * Format self_cost 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatSelfCostAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format set_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatSetIdAttribute($value)
    {
        return $this->fetchAttribute(Set::class, $value, 'type');
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