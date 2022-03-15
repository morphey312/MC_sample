<?php

namespace App\V1\Observers\HrPortal;

use App\V1\Jobs\HrPortal\CreateNewClinic;
use App\V1\Models\Clinic;

class CreateClinicObserver
{
    /**
     * Handle the clinic "created" event.
     *
     * @param Clinic $clinic
     * @return void
     */
    public function created(Clinic $clinic)
    {
        CreateNewClinic::dispatch($clinic->id, $clinic->name);
    }

}
