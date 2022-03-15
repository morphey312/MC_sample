<?php

namespace App\V1\Models\Call;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;
use Illuminate\Support\Arr;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Contracts\Services\Voip\SubResolver;
use App\V1\Traits\Models\HasCachedAttributes;
use App\V1\Models\SmsAppointmentReminder;
use App\V1\Contracts\Services\Messenger\Message;
use App;

class CallLog extends BaseModel implements ClinicShared
{
    use HasConstraint, HasCachedAttributes;
    
    const TYPE_INCOMING = 'incoming';
    const TYPE_OUTGOING = 'outgoing';
    
    const SOURCE_INTERNAL = 'internal';
    const SOURCE_CALL = 'call';
    const SOURCE_CALLBACK = 'callback';
    const SOURCE_WEBCALLBACK = 'webcallback';
    
    const STATUS_INITIATED = 'initiated';
    const STATUS_WAITING = 'waiting';
    const STATUS_ABANDONED = 'abandoned';
    const STATUS_IN_PROGRESS = 'progress';
    const STATUS_ENDED = 'ended';
    const STATUS_PARKED = 'parked';
    const STATUS_CONFERENCE = 'conference';
    
    const RELATION_TYPE = 'call_log';
    
    /**
     * @var string
     */
    protected $table = 'call_logs';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * @var array
     */
    protected $dates = [
        'started_at',
        'ended_at',
    ];
    
    /**
     * @var array
     */
    protected $casts = [
        'runtime_state' => 'array',
    ];
    
    /**
     * @var array
     */
    protected $deleting_constraints = [
        'process',
    ];
    
    /**
     * Related clinic
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    
    /**
     * Related caller
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function caller()
    {
        return $this->morphTo();
    }
    
    /**
     * Related callee
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function callee()
    {
        return $this->morphTo();
    }
    
    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    /**
     * Related sms reminders
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sms_reminders()
    {
        return $this->HasOne(SmsAppointmentReminder::class, 'call_log_id');
    }
    /**
     * Get person at the other end
     * 
     * @return mixed
     */ 
    public function getVersaAttribute()
    {
        return $this->type == self::TYPE_INCOMING ? $this->caller : $this->callee;
    }
    
    /**
     * Set person at the other end
     * 
     * @param mixed $value
     */ 
    public function setVersaAttribute($value)
    {
        if ($this->type == self::TYPE_INCOMING) {
            $this->caller = $value;
        } else {
            $this->callee = $value;
        }
    }
    
    /**
     * Get any contact related to phone number of this call
     * 
     * @return array
     */ 
    public function getRelatedContactsAttribute()
    {
        $resolver = App::make(SubResolver::class);
        $result = $resolver->resolveAll($this->phone_number);
        return $result['subjects'];
    }
    
    /**
     * Related process record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */ 
    public function process()
    {
        return $this->hasOne(ProcessLog::class, 'call_id')->orderBy('id', 'asc');
    }
    
    /**
     * Get runtime state value
     * 
     * @param string $prop
     * @param mixed $default
     * 
     * @return mixed
     */ 
    public function getRuntimeState($prop, $default = null)
    {
        return Arr::get($this->runtime_state, $prop, $default);
    }
    
    /**
     * Update call runtime state
     * 
     * @param array $stateChanges
     */ 
    public function updateRuntimeState(array $stateChanges)
    {
        $state = $this->runtime_state;
        
        foreach ($stateChanges as $prop => $value) {
            if ($value === null) {
                Arr::forget($state, $prop);
            } else {
                Arr::set($state, $prop, $value);
            }
        }
        
        $this->runtime_state = $state;
    }
    
    /**
     * Get caller runtime state value
     * 
     * @param string $caller
     * @param string $prop
     * @param mixed $default
     * 
     * @return mixed
     */ 
    public function getCallerState($caller, $prop = null, $default = null)
    {
        if ($prop === null) {
            return Arr::get($this->runtime_state, "caller.{$caller}", []);
        }
        return Arr::get($this->runtime_state, "caller.{$caller}.{$prop}", $default);
    }
    
    /**
     * Update caller runtime state
     * 
     * @param string $caller
     * @param array $stateChanges
     */ 
    public function updateCallerState($caller, array $stateChanges)
    {
        foreach ($stateChanges as $prop => $value) {
            $this->updateRuntimeState([
                "caller.{$caller}.{$prop}" => $value,
            ]);
        }
    }
    
    /**
     * Load call from array
     * 
     * @param array $data
     */ 
    public function loadFromArray(array $data) 
    {
        $this->setRawAttributes($data, true);
        $this->exists = !empty($data[$this->getKeyName()]);
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
    
    /**
     * @inherit
     */ 
    public function getBroadcastPayload()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'source' => $this->source,
            'phone_number' => $this->phone_number,
        ];
    }

    /**
     * Queue sms reminder for this call log
     * @param $template
     * @param $scheduled_at
     * @param string $type
     */ 
    
    public function queueSms($template, $scheduled_at, $type = SmsAppointmentReminder::TYPE_AUTO)
    {
        $this->sms_reminders()->create(
            [
                'template_id' => $template->id,
                'status' => Message::STATUS_NO_DELIVERY,
                'scheduled_at' => $scheduled_at,
                'type' => $type,
                'phone_number' => $this->phone_number
            ]
        );
    }
}
