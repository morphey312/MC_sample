<?php

namespace App\V1\Observers\Analysis\Laboratory;

use App\V1\Models\Analysis\Laboratory\TransferSheet;
use App\V1\Jobs\LaboratoryClient\SendTransfer;
use App\V1\Models\ActionLog;
use Illuminate\Support\Facades\Auth;

class TransferSheetObserver
{
    /**
     * @param TransferSheet $model
     */
    public function updated(TransferSheet $model) {
        if($model->isDirty('status') &&
            $model->status === TransferSheet::STATUS_TRANSPORTING) {
                SendTransfer::dispatch($model->id);
                $this->addLog($model, 'sended');
            }
    }

    /**
     * @param TransferSheet $model
     */
    public function created(TransferSheet $model)
    {   
        $this->addLog($model, 'created');
    }

    protected function addLog($model, $type = '')
    {
        $log = new ActionLog();
        $log->user_id = Auth::id();
        $log->loggable_id = $model->id;
        $log->loggable_type = TransferSheet::RELATION_TYPE;
        $log->new = [
            $type => true
        ];
        $log->old = null;
        $log->save();
    }
}
