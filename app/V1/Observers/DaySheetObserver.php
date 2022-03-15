<?php

namespace App\V1\Observers;

use Illuminate\Database\Eloquent\Model;
use App\V1\Jobs\WaitList\DaySheetNotification;
use App\V1\Models\Employee;
use Illuminate\Support\Facades\Auth;

class DaySheetObserver
{
     /**
     * Listen to timesheetSaved event
     * 
     * @param Model $model
     */ 
    public function timesheetSaved(Model $model)
    {
        if ($model->day_sheet_owner_type == Employee::RELATION_TYPE) {
            DaySheetNotification::dispatch($model->id, Auth::id());
        }
    }
}