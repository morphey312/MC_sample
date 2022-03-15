<?php

namespace App\V1\Models\Call;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;
use App\V1\Models\Call;
use App\V1\Models\Appointment;

class RelatedAction extends BaseModel
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    
    const TYPE_PATIENT = Patient::RELATION_TYPE;
    const TYPE_CALL = Call::RELATION_TYPE;
    const TYPE_APPOINTMENT = Appointment::RELATION_TYPE;
    const TYPE_CALL_LOG = CallLog::RELATION_TYPE;
    
    /**
     * @var array
     */
    protected $fillable = [
        'action',
        'time',
        'related_id',
        'related_type',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'call_related_actions';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * Related entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function related()
    {
        return $this->morphTo();
    }
}
