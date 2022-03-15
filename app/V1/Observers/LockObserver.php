<?php

namespace App\V1\Observers;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\DaySheet\LockLog;
use App\V1\Models\DaySheet\Lock;

class LockObserver
{
    /**
    * Listen to saved event
    *
    * @param Lock $model
    */
    public function saved(Lock $model)
    {
        /* $log = new LockLog();
        if (is_object($model) && $model->type == Lock::TYPE_FIXED) {
            foreach ($model->getAttributes() as $key => $value) {
                if ($key == 'id') {
                    $log->lock_id = $value;
                } else {
                    $log->{"$key"} = $value;
                }
            }
            $log->status = true;

            $ifExistArr = LockLog::where("day_sheet_id", "=", $model->day_sheet_id)
                ->where("type", Lock::TYPE_FIXED)
                ->where("start", "=", $model->start.":00")
                ->where("status", "=", true)
                ->first();

            if (!$ifExistArr) {
                $log->save();
            }
        } */
    }
}
