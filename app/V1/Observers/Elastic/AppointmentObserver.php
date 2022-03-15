<?php

namespace App\V1\Observers\Elastic;

use App\V1\Models\Appointment;
use App\V1\Jobs\Elastic\Report\Income\SingleAppointmentJob as IncomeReportAppointmentJob;
use App\V1\Jobs\Elastic\Report\Redirects\SingleAppointmentJob as RedirectsReportAppointmentJob;
use App\V1\Jobs\Elastic\Report\CallCenter\AppointmentSlicesJob;
use App\V1\Jobs\Elastic\Report\CallCenter\AppointmentBonusJob;

class AppointmentObserver
{
    /**
     * Listen to saved event
     * 
     * @param Appointment $model
     */ 
    public function saved(Appointment $model)
    {
        if (config('services.elasticsearch.enable_cache')) {
            IncomeReportAppointmentJob::dispatch($model->id)->onQueue('elastic');
            RedirectsReportAppointmentJob::dispatch($model->id)->onQueue('elastic');
            AppointmentSlicesJob::dispatch($model->id)->onQueue('elastic');
            AppointmentBonusJob::dispatch($model->id)->onQueue('elastic');
        }
    }
}