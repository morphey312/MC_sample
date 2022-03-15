<?php

namespace App\V1\Observers\Audit;

class UserAudit extends BaseRelationAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'login',
    ];
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {
        $log->loggable_id = $model->userable_id;
        $log->loggable_type = $model->userable_type;
    }
    
    /**
     * @inherit
     */ 
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();
        
        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + ($model->isDirty('password') ? ['password' => 'old'] : []);
    }
    
    /**
     * @inherit
     */ 
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model) 
            + $this->getCustomAttributes($model)
            + ($model->isDirty('password') ? ['password' => 'new'] : []);
    }
    
    /**
     * Get custom attributes from user model
     * 
     * @param \App\V1\Models\User $model
     * 
     * @return array
     */ 
    protected function getCustomAttributes($model)
    {
        return [
            'roles' => $model->roles->pluck('name')->all(),
            'permissions' => $model->permissions->pluck('description')->all(),
        ];
    }
}