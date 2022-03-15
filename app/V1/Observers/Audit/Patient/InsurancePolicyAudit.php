<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class InsurancePolicyAudit extends BaseAudit
{

    /**
     * @var array
     */ 
    protected $attributes = [
        'full_info',
    ];

    /**
     * Listen to created event
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->patient);
            $log->new = $this->getCurrentValues($model);
            $log->old = null;
            $log->save();
        }
    }


    /**
     * Listen to updating event
     * 
     * @param Model $model
     */ 
    public function saving(Model $model)
    {
        if (self::$auditEnabled && $model->exists && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->patient);
            $old = $this->getOriginalValues($model);
            $new = $this->getCurrentValues($model);
            $changed = $this->getChangedAttributes($new, $old);
            if (count($changed) !== 0) {
                $log->new = Arr::only($new, $changed);
                $log->old = Arr::only($old, $changed);
                $log->save();
            }
        }
    }

    /**
     * Listen to deleting event
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->patient);
            $log->old = $this->getOriginalValues($model);
            $log->new = null;
            $log->save();
        }
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
     * @param \App\V1\Models\Patient\InsurancePolicy $model
     * 
     * @return array
     */ 
    protected function getCustomAttributes($model)
    {
        $data = [];
        $data['policy'] = $model->full_info;
        return $data;
    }
}