<?php

namespace App\V1\Observers\Elastic;

use App\V1\Models\Patient;
use App\V1\Jobs\Elastic\Report\Redirects\SingleAppointmentJob as RedirectsReportAppointmentJob;

class PatientAppointmentObserver
{
    /**
     * Listen to saved event
     * 
     * @param Patient $model
     */ 
    public function saving(Patient $model)
    {
        if ($model->isDirty('source_id') && config('services.elasticsearch.enable_cache')) {
            $appointments = $model->appointments;
            foreach ($appointments as $appointment) {
                RedirectsReportAppointmentJob::dispatch($appointment->id)->onQueue('elastic');
            }
        }
    }
}