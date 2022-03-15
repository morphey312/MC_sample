<?php

namespace App\V1\Observers\HrPortal;

use App\V1\Models\Employee;
use App\V1\Jobs\RegisterOnPortal;

class RegisterOnPortalObserver
{
    /**
     * Listen to saving event
     *
     * @param Employee $model
     */
    public function updating(Employee $model)
    {
        if ($model->copy_to_portal && $model->isDirty('copy_to_portal')) {
            RegisterOnPortal::dispatch($model);
        }
    }
}
