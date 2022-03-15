<?php

namespace App\V1\Observers\Audit;

class BaseRelationAudit extends BaseAudit
{
    /**
     * @var string
     */ 
    protected $foreignKey;
    
    /**
     * @var string
     */
    protected $relatedType;
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {
        $log->loggable_id = $model->getAttribute($this->foreignKey);
        $log->loggable_type = $this->relatedType;
    }
}