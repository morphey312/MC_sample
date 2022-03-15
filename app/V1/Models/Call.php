<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\CallRequest;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use Illuminate\Support\Arr;

class Call extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    const TYPE_ALL = 'all';
    const TYPE_DELETED = 'deleted';
    const TYPE_NOT_DELETED = 'not_deleted';
    
    const RELATION_TYPE = 'call';

    /**
     * @var array
     */
    protected $fillable = [
        'comment',
        'time',
        'date',
        'is_first',
        'call_result_id',
        'clinic_id',
        'employee_id',
        'contact',
        'specialization_id',
        'call_request_id',
        'call_delete_reason_id',
        'delete_reason_comment',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->refresh();
            
            if ($model->hasCallRequestWithStatus(CallRequest::STATUS_MADE)) {
                $model->updateCallRequestStatus(CallRequest::STATUS_SUCCESS_CALL);
            }
        });
    }

    /**
     * Related call results
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call_result()
    {
        return $this->belongsTo(Call\Result::class, 'call_result_id');
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
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Related call request
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call_request()
    {
        return $this->belongsTo(CallRequest::class);
    }

    /**
     * Related call delete reason
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call_delete_reason()
    {
        return $this->belongsTo(Call\DeleteReason::class, 'call_delete_reason_id');
    }

    /**
     * Update related call request status
     * 
     * @param string $status
     */ 
    public function updateCallRequestStatus($status)
    {
        if($call_request = $this->call_request) {
            $call_request->status = $status;
            $call_request->save();    
        }
    }

    /**
     * Check if has related call request with status "made"
     * 
     * @param string $status
     */ 
    public function hasCallRequestWithStatus($status) 
    {
        return $this->call_request()->exists() && $this->call_request->status == $status;
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related wait list record
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wait_list_record()
    {
        return $this->hasOne(WaitListRecord::class, 'call_id');
    }

    /**
     * Save wait list record
     * 
     * @param array $attributes
     */
    public function saveWaitListRecord($attributes)
    {
        $id = Arr::get($attributes, 'id', null);
        $cancel_reason = Arr::get($attributes, 'cancel_reason', null);
        $waitListRecord = WaitListRecord::firstOrNew(['id' => $id]);

        if ($cancel_reason != null) {
            $waitListRecord->cancel_reason = $cancel_reason;
            $waitListRecord->status = WaitListRecord::STATUS_CANCELED;
        } else {
            $waitListRecord->period_from = Arr::get($attributes, 'period_from', null);
            $waitListRecord->period_to = Arr::get($attributes, 'period_to', null);
            $waitListRecord->doctor_id = Arr::get($attributes, 'doctor_id', null);
            $waitListRecord->clinic_id = $this->clinic_id;
            $waitListRecord->patient_id = $this->contact_id;
            $waitListRecord->call_id = $this->id;
            $waitListRecord->specialization_id = $this->specialization_id;
            $waitListRecord->operator_id = Arr::get($attributes, 'operator_id', null);
        }
        $waitListRecord->save();
    }

    /**
     * Verify call result is for add record to wait list
     * 
     * @return bool
     */
    public function shouldAddToWaitList()
    {
        return $this->call_result->for_wait_list;
    }
}
