<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class IssuedMedicineAudit extends BaseAudit
{

    /**
     * Listen to created event
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->assigned_medicine);
            $log->new = ['issued' => $model->quantity];
            $log->old = null;
            $log->save();
        }
    }
}
