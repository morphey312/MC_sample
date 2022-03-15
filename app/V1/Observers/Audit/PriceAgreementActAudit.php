<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\ActionLog;
use App\V1\Models\PriceAgreementAct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class PriceAgreementActAudit extends BaseAudit
{
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
        $log->loggable_id = $model->id;
        $log->loggable_type = PriceAgreementAct::RELATION_TYPE;

    }

}
