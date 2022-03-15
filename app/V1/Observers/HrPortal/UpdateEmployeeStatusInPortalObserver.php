<?php

namespace App\V1\Observers\HrPortal;

use App\V1\Models\Employee\Clinic;
use App\V1\Jobs\HrPortal\UpdateEmployeeStatus;

class UpdateEmployeeStatusInPortalObserver
{
     /**
     * Handle the clinic "updated" event.
     *
     * @param \App\V1\Models\Employee\Clinic $clinic
     * @return void
     */
    public function updated(Clinic $clinic)
    {
        if ($clinic->isDirty('status')) {
            UpdateEmployeeStatus::dispatch($clinic->employee_id, $clinic->status);
        }
    }

}
