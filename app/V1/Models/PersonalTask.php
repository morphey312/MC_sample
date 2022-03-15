<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use Auth;

class PersonalTask extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    
    const RELATION_TYPE = 'personal_task';
    
    const STATUS_NEW = 'new';
    const STATUS_STARTED = 'started';
    const STATUS_DEFERRED = 'deferred';
    const STATUS_COMPLETED = 'completed';
    
    /*
     * @array
     */
    protected $fillable = [
        'operator_id',
        'date',
        'comment',
        'attachments',
        'clinic_id',
        'specialization_id',
        'patients',
        'status',
        'outcome',
        'feedback_attachments',
        'modified_at',
        'status_changed_at',
    ];
    
    /*
     * @array
     */
    protected $dates = [
        'date',
        'modified_at',
        'status_changed_at',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->employee = Auth::user()->getEmployeeModel();
        });
    }

    /**
     * Related employee with operator role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
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
     * Related patients
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'personal_task_patients', 'task_id', 'patient_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    /**
     * Related attachments
     * 
     * @return \App\V1\Repositories\Relations\FileAttachment
     */ 
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }
    
    /**
     * Related feedback attachments
     * 
     * @return \App\V1\Repositories\Relations\FileAttachment
     */ 
    public function feedback_attachments()
    {
        return $this->fileAttachment('feedback_attachments');
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
