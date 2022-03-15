<?php

namespace App\V1\Observers;

use App\V1\Mailing\Appointments\AppointmentMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\V1\Jobs\WaitList\DaySheetNotification;
use App\V1\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\V1\Contracts\Repositories\DaySheetRepository;
use Illuminate\Support\Facades\Log;
use MailingMessenger;

class AppointmentObserver
{
    /**
     * Listen to timesheetSaved event
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        if ($model->doctor_type === Employee::RELATION_TYPE) {
            $daySheet = null;
            if($model->isDirty('date')){
                $daySheet = $this->getDaySheet($model, $model->getOriginal('date'));
            }elseif (($model->is_deleted && !$model->getOriginal('is_deleted')) ||
                $this->appointmentTimeChanged($model)
            ) {
                $daySheet = $this->getDaySheet($model, $model->date);
            }
            if ($daySheet !== null) {
                DaySheetNotification::dispatch($daySheet->id, Auth::id());
            }
        }

        //todo remove after test
        Log::channel('esputnik_test')->info('AppointmentObserver-Updated');
        MailingMessenger::send(new AppointmentMessage($model));
    }

    /**
     * Get doctor day sheet
     *
     * @param Appointment $appointment
     *
     * @return mixed
     */
    protected function getDaySheet($appointment, $date)
    {
        $repository = app(DaySheetRepository::class);
        return $repository->getFilteredQuery([
            'employees' => $appointment->doctor_id,
            'date' => $date,
            'clinic' => $appointment->clinic_id,
        ])->first();
    }

    /**
     * Check if appointment time was changed
     *
     * @param $appointment
     *
     * @return bool
     */
    protected function appointmentTimeChanged(&$appointment)
    {
        $newDuration = $this->getAppointmentDuration($appointment->start,$appointment->end);
        $oldDuration = $this->getAppointmentDuration($appointment->getOriginal('start'),$appointment->getOriginal('end'));

        return $newDuration < $oldDuration;
    }

    /**
     * Get appointment duration
     *
     * @param $start
     * @param $end
     *
     * @return mixed
     */
    protected function getAppointmentDuration($start,$end)
    {
        return Carbon::parse($start)->diffInMinutes($end);
    }

    /**
     * Listen to Saved event
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        //todo remove after test
        Log::channel('esputnik_test')->info('AppointmentObserver-Created');
        MailingMessenger::send(new AppointmentMessage($model));
    }
}
