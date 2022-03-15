<?php

namespace App\V1\Observers\Appointment;

use App\V1\Models\Appointment;
use App\V1\Http\Resources\AppointmentBroadcastResource;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Events\Broadcast\AppointmentUpdated;
use App;
use Illuminate\Support\Facades\Log;

class AmbulanceStatusObserver
{
    /**
     * Listen to updated event
     *
     * @param Appointment $model
     */
    public function updated(Appointment $model)
    {
        $ambulanceEnRouteID = Appointment::getStatusAmbulanceEnRoute();
        $ambulanceCallTransferred = Appointment::getStatusAmbulanceCallTransferred();
        $cameToDoctor =  Appointment::getStatusCameToDoctor();

        $trackedStatuses = [
            $ambulanceEnRouteID,
            $ambulanceCallTransferred,
            $cameToDoctor
        ];
       
        if ($model->isDirty('appointment_status_id') and in_array($model->appointment_status_id, $trackedStatuses)) {
            if (!empty($model->ambulance_call)) {
                switch ($model->appointment_status_id) {
                    case $ambulanceCallTransferred:
                        $model->ambulance_call->call_transferred_time = now();
                        $model->ambulance_call->save();
                        break;
                    case $ambulanceEnRouteID:
                        $model->ambulance_call->en_route_time = now();
                        $model->ambulance_call->save();
                        break;
                    case $cameToDoctor:
                        $model->ambulance_call->arrival_time = now();

                        if (!empty($model->ambulance_call->en_route_time)) {
                            $model->ambulance_call->en_route_overall_minutes =
                                $model->ambulance_call->en_route_time->diffInMinutes($model->ambulance_call->arrival_time);
                        }

                        $model->ambulance_call->save();
                        break;
                }
            }
        }
    }
}
