<?php

namespace App\V1\Observers\Audit\InsuranceCompany;

use App\V1\Models\InsuranceCompany\Act;
use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\ActionLog;
use App\V1\Models\Clinic;
use App\V1\Models\InsuranceCompany;
use Auth;

class ActAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'clinic_id',
        'insurance_company_id',
        'amount',
        'number',
        'comment',
    ];

    /**
     * Listen to created event
     * 
     * @param Act $model
     */ 
    public function printed($model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model);
            $log->new = ['printed' => true];
            $log->old = null;
            $log->save();
        }
    }
     /**
     * Listen to created model
     * 
     * @param Act $model
     */ 
    public function created($model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model);
            $log->new = ['created' => now()];
            $log->old = null;
            $log->save();
        }
    }

     /**
     * Listen to update status
     * 
     * @param Act $model
     */ 
    public function updated($model)
    {
        if (self::$auditEnabled && Auth::id()) {
            if($model->isDirty('status') && $model->status != Act::ACT_STATUS_CREATED){
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
                $log->old = null;
                $log->new = ['status_changed' => $model->status];
                $log->save();
            }    
        }
    }
    /**
     * Format clinic 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatClinicIdAttribute($value)
    {
        return $this->fetchAttribute(Clinic::class, $value, 'name');
    }

    /**
     * Format insurance company 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatInsuranceCompanyIdAttribute($value)
    {
        return $this->fetchAttribute(InsuranceCompany::class, $value, 'name');
    }

    /**
     * Format amount
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatAmountAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }
}