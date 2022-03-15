<?php

namespace App\V1\Observers\Audit;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\DaySheet;

class DaySheetAudit extends BaseRelationAudit
{
    /**
     * @inherit
     */ 
    public function created(Model $model)
    {
        // Do nothing
    }
    
    /**
     * @inherit
     */ 
    public function saving(Model $model)
    {
        // Do nothing
    }
    
    /**
     * @inherit
     */ 
    public function deleting(Model $model)
    {
        // Do nothing
    }
    
    /**
     * Listen to timesheetSaved event
     * 
     * @param Model $model
     */ 
    public function timesheetSaved(Model $model)
    {
        return parent::created($model);
    }
    
    /**
     * Listen to timesheetDeleting event
     * 
     * @param Model $model
     */ 
    public function timesheetDeleting(Model $model)
    {
        return parent::deleting($model);
    }
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {
        $log->loggable_id = $model->day_sheet_owner_id;
        $log->loggable_type = DaySheet::RELATION_TYPE;
        $log->category = $model->day_sheet_owner_type;
    }
    
    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();
        
        return $this->getCustomAttributes($fresh);
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return $this->getCustomAttributes($model);
    }
    
    /**
     * Get custom attributes from day sheet model
     * 
     * @param \App\V1\Models\User $model
     * 
     * @return array
     */ 
    protected function getCustomAttributes($model)
    {
        return [
            'date' => $this->formatDate($model->date),
            'clinic' => $model->clinic->name,
            'time' => $model->time_sheets->map(function($time) {
                return sprintf(
                    '%s - %s (%s)', 
                    $this->formatTime($time->time_from), 
                    $this->formatTime($time->time_to), 
                    $time->specializations->implode('name', ', ')
                );
            })->all(),
        ];
    }
}