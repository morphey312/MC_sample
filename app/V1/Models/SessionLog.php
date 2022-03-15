<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Call\ProcessLog;
use Auth;
use App\V1\Contracts\Repositories\Call\ProcessLogRepository;

class SessionLog extends BaseModel
{
    const ACTION_SESSION_START = 'session-start';
    const ACTION_SESSION_END = 'session-end';
    const ACTION_PAUSE_START = 'pause-start';
    const ACTION_PAUSE_END = 'pause-end';
    const ACTION_CALL_START = 'call-start';
    const ACTION_CALL_END = 'call-end';
    const ACTION_CONFERENCE_START = 'conference-start';
    const ACTION_CONFERENCE_END = 'conference-end';
    const ACTION_WRAPUP_START = 'wrapup-start';
    const ACTION_WRAPUP_END = 'wrapup-end';
    
    /**
     * @var array
     */
    protected $fillable = [
        'sip',
        'phone_number',
        'action',
        'duration',
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
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Get process log completed with this entry
     * 
     * @return ProcessLog
     */ 
    public function getProcessLog()
    {
        if ($this->action === self::ACTION_WRAPUP_END) {
            $processLogRepository = app(ProcessLogRepository::class);
            return $processLogRepository->getLatestLog($processLogRepository->filter([
                'operator' => $this->employee_id,
                'created_from' => $this->created_at,
                'created_to' => $this->created_at->subSeconds($this->duration),
            ]));
        }
        
        return null;
    }
}