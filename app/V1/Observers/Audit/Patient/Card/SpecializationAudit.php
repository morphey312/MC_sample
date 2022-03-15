<?php

namespace App\V1\Observers\Audit\Patient\Card;

use App\V1\Observers\Audit\BaseRelationAudit;
use App\V1\Models\Patient;

class SpecializationAudit extends BaseRelationAudit
{
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {
        $log->loggable_id = $model->card->patient_id;
        $log->loggable_type = Patient::RELATION_TYPE;
    }
    
    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        return [
            'cards' => sprintf('%s - %s%s', $model->card->clinic->name, $model->card->number, $model->specialization->short_name),
        ];
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return [
            'cards' => sprintf('%s - %s%s', $model->card->clinic->name, $model->card->number, $model->specialization->short_name),
        ];
    }
}