<?php

namespace App\V1\Observers;

use App\V1\Models\Price;
use App\V1\Models\WaitListRecord;

class PriceObserver
{
    /**
     * Listen to updating event
     *
     * @param Price $model
     */
    public function updated(Price $model)
    {
        $service = $model->service()->with('active_base_prices')->first();
        $clinicsWithActiveTariff = $service->active_base_prices->pluck('clinics')->flatten()->pluck('id')->toArray();

        $serviceId = $model->service_id;
        $records = WaitListRecord::whereIn('status', [WaitListRecord::STATUS_NEW, WaitListRecord::STATUS_PAUSE])
            ->whereHas('prepayment_service', function($query) use ($serviceId) {
                $query->where('service_id', '=', $serviceId)
                    ->where('service_type', '=' , 'service');
            })->get();

        foreach ($records as $record) {
            if (in_array($record->clinic_id, $clinicsWithActiveTariff)) {
                $status = WaitListRecord::STATUS_NEW;
            } else {
                $status = WaitListRecord::STATUS_PAUSE;
            }

            $record->status = $status;
            $record->save();
        }
    }
}
