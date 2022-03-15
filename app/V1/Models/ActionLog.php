<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use Carbon\Carbon;

class ActionLog extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'action_logs';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * @var array
     */ 
    protected $casts = [
        'new' => 'array',
        'old' => 'array',
    ];
    
    /**
     * @var array
     */
    protected $dates = [
        'created_at',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = Carbon::now();
        });
    }
    
    /**
     * Related loggable record
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function loggable()
    {
        return $this->morphTo();
    }
    
    /**
     * Related user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Create new loggable instance
     * 
     * @param array $data
     * 
     * @return BaseModel
     */ 
    protected function createLoggableInstance($data = [])
    {
        $class = BaseModel::getActualClassNameForMorph($this->loggable_type);
        $instance = new $class();
        $instance->setRawAttributes($data);
        return $instance;
    }
    
    /**
     * Get model in old state
     * 
     * @return BaseModel
     */ 
    public function getOldState()
    {
        if ($this->old === null) {
            return null;
        }
        return $this->createLoggableInstance($this->old);
    }
    
    /**
     * Get model in new state
     * 
     * @return BaseModel
     */ 
    public function getNewState()
    {
        if ($this->new === null) {
            return null;
        }
        return $this->createLoggableInstance($this->new);
    }
}
