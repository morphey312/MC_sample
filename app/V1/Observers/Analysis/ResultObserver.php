<?php

namespace App\V1\Observers\Analysis;

use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Mailing\Analysis\AnalysisResultSavedMessage;
use App\V1\Models\Analysis\Result;
use MailingMessenger;


class ResultObserver
{
    /**
     * Listen to updated event
     *
     * @param Result $model
     */
    public function saved(Result $model)
    {
        if (in_array($model->delivery_status, [
                Message::STATUS_NO_DELIVERY,
                Message::STATUS_RE_DELIVERY,
            ]) && $model->attachments->count() !== 0)
        {
            $this->sendResultToEmail($model);
        }

        MailingMessenger::send(new AnalysisResultSavedMessage($model));
    }

    /**
     * Listen to updated event
     *
     * @param Result $model
     */
    public function saving(Result $model)
    {
        $model->updateStatus();
    }

    /**
     * Send result to patient email if possible
     *
     * @param Result $model
     */
    protected function sendResultToEmail($model)
    {
        $patient = $model->patient;
        if ($patient->mailing_analysis == 1) {
            if ($model->by_policy != false) {
                $model->sendToPatient();
                return;
            }

            if ($patient->legal_entity_id != null) {
                $appointment = $model->appointment;
                if ($appointment && $patient->legal_entity_id == $appointment->legal_entity_id) {
                    $model->sendToPatient();
                    return;
                }
            }

            $serviceContainer = $model->appointment_service_item
                ? $model->appointment_service_item->appointment_service
                : null;
            if ($serviceContainer !== null && $serviceContainer->payed >= $serviceContainer->cost) {
                $model->sendToPatient();
            }
        }
    }
}
