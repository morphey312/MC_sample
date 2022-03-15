<?php

namespace App\V1\Observers\Appointment;

use App\V1\Models\Appointment;
use App\V1\Models\CallRequest;
use App\V1\Models\CallRequest\Purpose;
use Carbon\Carbon;

class CallRequestObserver
{
    /**
     * Listen to created event
     *
     * @param Appointment $model
     */
    public function created(Appointment $model)
    {
        $this->processSignUpCallRequest($model);
        $this->createCallRequest($model);
    }

    /**
     * Listen to updated event
     *
     * @param Appointment $model
     */
    public function updated(Appointment $model)
    {
        if ($model->is_deleted) {
            $this->cancelCallRequest($model);
        }
        if($model->is_first === 1 && ($model->date != $model->getOriginal('date')) &&
            !$model->existing_call_request()->exists()) {
            $this->createCallRequest($model);
        }
    }

    /**
     * Check if appointment date is greater than minimum days for call request
     * and it is the first visit
     *
     * @param Appointment $model
     *
     * @return bool
     */
    protected function appointmentNeedCallRequest($model)
    {
        return $model->is_first == 1
            && $model->date > today()
            && today()->diffInDays(Carbon::parse($model->date)) >= Appointment::CALL_REQUEST_MINIMUM_DAYS;
    }

    /**
     * Automatic creating related call request
     *
     * @param Appointment $model
     */
    protected function createCallRequest($model)
    {
        if ($this->appointmentNeedCallRequest($model)) {
            $day = Carbon::parse($model->date)->subDay()->toDateString();
            $call_request = new CallRequest();
            $call_request->recall_from = $day;
            $call_request->recall_to = $day;
            $call_request->specialization_id = $model->specialization_id;
            $call_request->clinic_id = $model->clinic_id;
            $call_request->doctor_id = $model->doctor_id;
            $call_request->doctor_type = $model->doctor_type;
            $call_request->patient_id = $model->patient_id;
            $call_request->status = CallRequest::STATUS_MADE;
            $call_request->added = CallRequest::ADDED_TYPE_AUTO;
            $call_request->call_request_purpose = $this->getAutoCallRequestPurpose($model);
            $model->call_request()->save($call_request);
        }
    }

    /**
     * Get call request purpose for auto call request
     *
     * @param Appointment $model
     *
     * @return Purpose
     */
    protected function getAutoCallRequestPurpose($model)
    {
        return Purpose::where('auto_add', '=', 1)
            ->where('company_id', '=', $model->company_id)
            ->first();
    }

    /**
     * Set related call request status and comment
     *
     * @param Appointment $model
     */
    protected function cancelCallRequest($model)
    {
        if ($call_request = $model->call_request) {
            $call_request->status = CallRequest::STATUS_CANCELED;
            $call_request->comment_canceled = CallRequest::COMMENT_CANCELED_APPOINTMENT;
            $call_request->save();
        }
    }

    /**
     * Process sign up call request
     *
     * @param Appointment $model
     */
    protected function processSignUpCallRequest($model)
    {
        CallRequest::where('specialization_id', '=', $model->specialization_id)
            ->where('clinic_id', '=', $model->clinic_id)
            ->where('patient_id', '=', $model->patient_id)
            ->where('status', '=', CallRequest::STATUS_MADE)
            ->whereIn('call_request_purpose_id', function($query) {
                $query->select('call_request_purposes.id')
                    ->from('call_request_purposes')
                    ->where('auto_next_visit', '=', 1);
            })
            ->update([
                'status' => CallRequest::STATUS_SIGNED_UP,
                'appointment_id' => $model->id
            ]);
    }
}
