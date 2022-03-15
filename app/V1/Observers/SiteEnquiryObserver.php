<?php

namespace App\V1\Observers;

use App\V1\Contracts\Repositories\PatientRepository;
use App\V1\Models\Call\ProcessLog;
use App\V1\Models\Patient\ClientIds;
use App\V1\Models\SiteEnquiry;
use App\V1\Events\Broadcast\NewSiteEnquiry;
use Illuminate\Support\Facades\Log;

class SiteEnquiryObserver
{
    /**
     * Listen to created event
     *
     * @param SiteEnquiry $model
     */
    public function created(SiteEnquiry $model)
    {
        if ($model->extract('operator.user.id')) {
            broadcast(new NewSiteEnquiry($model));
        }
    }

    /**
     * Listen to saved event
     *
     * @param ProcessLog $model
     */
    public function saved (SiteEnquiry $model)
    {
        if ($model->patient_id && $model->client_id) {
            $patient = $model->patient;
            $clientIdExists = $patient->client_ids()->where('value', '=', $model->client_id)->exists();

            if (!$clientIdExists) {
                $patient->client_ids()->create(['value' => $model->client_id]);
            }
        }
    }
}
