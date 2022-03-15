<?php

namespace App\V1\Observers\Audit\PriceAgreementAct;

use App\V1\Models\ActionLog;
use App\V1\Models\PriceAgreementAct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\V1\Observers\Audit\BaseAudit;

class PriceAudit extends BaseAudit
{   
    /**
     * Listen to updated event
     *
     * @param Model $model
     */
    public function saving(Model $model)
    {
        if (self::$auditEnabled && Auth::id() && $model->isDirty('recommended_cost') && $model->exists) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model);
            $log->old = [
                'recommended' => $model->service->name. ': ' . $model->recommended_cost
            ];
            $log->new = null;
            $log->save();
        }
    }
    /**
     * @inherit
     */
    public function created(Model $model) {
        //do nothing
    }
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {  
        $log->loggable_id = $model->price_agreement_act->id;
        $log->loggable_type = PriceAgreementAct::RELATION_TYPE;

    }

     /**
     * Listen to deleting event
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $this->safely(function() use($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
                $log->old = null;
                $log->new = [
                    'deleted' => $model->service->name
                ];
                $log->save();
            });
        }
    }
}
