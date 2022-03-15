<?php

namespace App\V1\Observers\Audit\Analysis\Laboratory;

use App\V1\Models\ActionLog;
use App\V1\Models\Appointment;
use App\V1\Observers\Audit\BaseAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class RegistrationBiomaterialAudit extends BaseAudit
{
    /**
     * @inherit
     */
    public function created(Model $model) {
        $log = new ActionLog();
        $log->user_id = Auth::id();
        $this->associate($log, $model);
        $log->new = [
            'biomaterial_register' => true
        ];
        $log->old = null;
        $log->save();
     
    }
    
    /**
     * @inherit
     */ 
    protected function associate($log, $model) 
    {  
        $log->loggable_id = $model->analysis_result->appointment->id;
        $log->loggable_type = Appointment::RELATION_TYPE;
    }
}
