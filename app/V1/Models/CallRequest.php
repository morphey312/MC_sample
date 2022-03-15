<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Facades\Auth;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class CallRequest extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;
    
    const STATUS_MADE = 'made';
    const STATUS_CANCELED = 'canceled';
    const STATUS_SUCCESS_CALL = 'success_call';
    const STATUS_SIGNED_UP = 'signed_up';
    const ADDED_TYPE_MANUAL = 'manual';
    const ADDED_TYPE_AUTO = 'auto';
    const COMMENT_CANCELED_APPOINTMENT = 'удаление записи пациента к врачу';
    
    const RELATION_TYPE = 'call_request';
    
    /**
     * @var array
     */
    protected $fillable = [
        'recall_from',
        'recall_to',
        'recommended_appointment_date',
        'status',
        'original_status',
        'added',
        'comment',
        'comment_canceled',
        'call_request_purpose_id',
        'patient_id',
        'call_id',
        'clinic_id',
        'appointment_id',
        'specialization_id',
        'doctor_id',
        'doctor_type',
    ];
    
    /**
     * @var array
     */
    protected $deleting_constraints = [
        'call',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->operator = Auth::user()->getEmployeeModel();
        });
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
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->morphTo();
    }

    /**
     * Related call request purpose
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call_request_purpose()
    {
        return $this->belongsTo(CallRequest\Purpose::class, 'call_request_purpose_id');
    }

    /**
     * Related call
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call()
    {
        return $this->hasOne(Call::class, 'call_request_id');
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
    
    /**
     * Related operator
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
    
    /**
     * Get request display string
     * 
     * @return string
     */ 
    public function getNameAttribute()
    {
        return sprintf('%s %s-%s', $this->call_request_purpose->name, $this->recall_from, $this->recall_to);
    }

    /**
     * @inherit
     */ 
    public function getBroadcastPayload()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
        ];
    }
}