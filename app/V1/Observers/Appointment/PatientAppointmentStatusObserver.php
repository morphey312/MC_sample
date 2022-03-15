<?php

namespace App\V1\Observers\Appointment;

use App\V1\Models\Appointment;
use App\V1\Http\Resources\AppointmentBroadcastResource;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Events\Broadcast\AppointmentUpdated;
use App;

class PatientAppointmentStatusObserver
{
    /**
     * Prevents chain reaction
     * 
     * @var bool
     */
    protected static $ignoreEvents = false;
    /**
     * Listen to updated event
     *
     * @param Appointment $model
     */
    public function updated(Appointment $model)
    {
        if (self::$ignoreEvents === false) {
            self::$ignoreEvents = true;
            if ($model->isDirty('appointment_status_id')) {
                $signedUpId = Appointment::getStatusSignedUp();
                if ($model->getOriginal('appointment_status_id') === $signedUpId) {
                    $onReceptionId = Appointment::getStatusOnReception();
                    if ($model->appointment_status_id === $onReceptionId) {
                        $repository = App::make(AppointmentRepository::class);
                        $appointments = $repository->getPatientClinicAppointments(
                            $model->patient_id, $model->date, $model->clinic_id, $signedUpId
                        );
                        foreach ($appointments as $appointment) {
                            $appointment->appointment_status_id = $onReceptionId;
                            $appointment->save();
                            $broadcastData = new AppointmentBroadcastResource($appointment);
                            broadcast(new AppointmentUpdated($broadcastData));
                        }
                    }
                }
            }
            self::$ignoreEvents = false;
        }
    }
}
