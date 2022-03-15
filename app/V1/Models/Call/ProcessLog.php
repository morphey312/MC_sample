<?php

namespace App\V1\Models\Call;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Employee;
use App\V1\Models\SiteEnquiry;
use App\V1\Models\Appointment;
use Auth;
use Carbon\Carbon;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\WaitListRecord;

class ProcessLog extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_PROCESSED = 'processed';
    const STATUS_NONPROCESSED = 'nonprocessed';
    const STATUS_IMPROCESSIBLE = 'improcessible';
    
    const PATIENT_TYPE_PATIENT = 1;
    const PATIENT_TYPE_NOT_PATIENT = 0;
            
    const VISIT_TYPE_FIRST = 1;
    const VISIT_TYPE_RETURN = 0;
    
    const IMPROCESSIBLE_REASON_OTHER = 0;
    
    /**
     * @var string
     */
    protected $table = 'call_process_logs';
    
    /**
     * @var array
     */
    protected $fillable = [
        'call',
        'enquiry',
        'wait_list_record',
        'status',
        'unprocessibility_reason',
        'unprocessibility_reason_comment',
        'comment',
        'is_patient',
        'is_first_visit',
        'source',
        'clinic',
        'contact_id',
        'contact_type',
        'is_incoming_call',
        'sip_number',
        'phone_number',
        'started_at',
        'related_actions',
    ];
    
    /**
     * @var array
     */
    protected $dates = [
        'started_at',
        'processed_at',
    ];
    
    /**
     * @var array
     */
    protected $cascade_delete = [
        'related_actions',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->operator = Auth::user()->getEmployeeModel();
            $model->processed_at = Carbon::now();
        });
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
     * Related contact
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function contact()
    {
        return $this->morphTo();
    }
    
    /**
     * Related operator
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }
    
    /**
     * Related call
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function call()
    {
        return $this->belongsTo(CallLog::class, 'call_id');
    }
    
    /**
     * Related enquiry
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function enquiry()
    {
        return $this->belongsTo(SiteEnquiry::class, 'enquiry_id');
    }
    
    /**
     * Related process log
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function auto_process()
    {
        return $this->belongsTo(static::class, 'auto_process_id');
    }
    
    /**
     * Related actions
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function related_actions()
    {
        return $this->hasMany(RelatedAction::class, 'process_log_id');
    }
    
    /**
     * Check if process log has related call
     * 
     * @return bool
     */ 
    public function hasCall()
    {
        return !empty($this->call_id);
    }
    
    /**
     * Check if process log has related enquiry
     * 
     * @return bool
     */ 
    public function hasEnquiry()
    {
        return !empty($this->enquiry_id);
    }

    /**
     * Check if process log has related wait list record
     * 
     * @return bool
     */ 
    public function hasWaitListRecord()
    {
        return !empty($this->wait_list_record_id);
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related wait_list_record
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function wait_list_record()
    {
        return $this->belongsTo(WaitListRecord::class, 'wait_list_record_id');
    }
}