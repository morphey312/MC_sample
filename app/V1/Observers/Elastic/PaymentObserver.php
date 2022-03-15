<?php

namespace App\V1\Observers\Elastic;

use App\V1\Models\Payment;
use App\V1\Jobs\Elastic\Report\Income\SinglePaymentJob;

class PaymentObserver
{
    /**
     * Listen to saved event
     * 
     * @param Payment $model
     */ 
    public function saved(Payment $model)
    {
        if (config('services.elasticsearch.enable_cache')) {
            SinglePaymentJob::dispatch($model->id)->onQueue('elastic');
        }
    }
}