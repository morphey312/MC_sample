<?php

namespace App\V1\Observers\Elastic\CallCenter;

use App\V1\Models\Call;
use App\V1\Jobs\Elastic\Report\CallCenter\CallSlicesJob;
use App\V1\Jobs\Elastic\Report\CallCenter\CallBonusJob;

class CallObserver
{
    /**
     * Listen to saved event
     * 
     * @param Call $model
     */ 
    public function saved(Call $model)
    {
        if (config('services.elasticsearch.enable_cache')) {
            CallSlicesJob::dispatch($model->id)->onQueue('elastic');
            CallBonusJob::dispatch($model->id)->onQueue('elastic');
        }
    }
}