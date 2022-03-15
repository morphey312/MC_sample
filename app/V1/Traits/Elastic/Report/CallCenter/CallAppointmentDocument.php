<?php

namespace App\V1\Traits\Elastic\Report\CallCenter;

use App\V1\Facades\ElasticsearchClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait CallAppointmentDocument
{
    /**
     * Get model query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        //
    }

    /**
     * Get document
     *
     * @param App\V1\Models\Call|App\V1\Models\Appointments $model
     *
     * @return array
     */
    protected function getDocument($model)
    {
        $isTelegramBot = false;

        if (
            ($model->is_call == 1 && $model->is_first == 1 && $model->is_appointment == 1) ||
            ($model->is_call == 1 && $model->is_appointment != 1)
        ) {
            $isTelegramBot = true;
        }

        $full_date =  Carbon::parse($model->date);

        return [
            'id' => $this->getModelId($model),
            'date' => $this->convertDate($model->date),
            'clinic_id' => $model->clinic_id,
            'operator_id' => $model->operator_id,
            'operator_name' => $model->operator_name,
            'specialization_id' => $model->specialization_id,
            'specialization' => $model->specialization,
            'is_non_profile' => $model->is_non_profile,
            'is_call' => $model->is_call,
            'is_appointment' => $model->is_appointment,
            'is_income' => $model->is_income,
            'is_treatment' => $model->is_treatment,
            'is_marketing' => $model->is_marketing,
            'is_reception' => $model->is_reception,
            'position_id' => $model->position_id,
            'appointment_source_id' => $model->appointment_source_id,
            'patient_source_id' => $this->getPatientSourceId($model->appointment_source_id, $model->patient_source_id),
            'patient_location' => $model->patient_location,
            'is_first' => $model->is_first,
            'is_call_for_tg_report' => $isTelegramBot,
            'time' => $model->time,
            'full_date' => $full_date->timestamp
        ];
    }

    /**
     *
     *
     * @return string
     */
    protected function getModelId($model)
    {
        if ($model->is_income) {
            return static::PREFIX_INCOME . $model->id; //appointment which is income
        }

        if ($model->is_treatment) {
            return static::PREFIX_TREATMENT . $model->id; //appointment which is treatment
        }

        if ($model->is_call) {
            if ($model->is_appointment) {
                return static::PREFIX_APPOINTMENT_CALL . $model->id; //appointment which counts as call
            }
            return static::PREFIX_CALL . $model->id; //call
        }
        return $model->id;
    }

    /**
     * Create index if not exists
     */
    protected function verifyIndexExists()
    {
        if (!ElasticsearchClient::indexExists($this->indexName)) {
            ElasticsearchClient::createIndex($this->indexName);
        }
    }

    /**
     * Get patient source id if appointment source id not exists
     *
     * @param $appointmentSourceId
     * @param $patientSourceId
     * @return mixed
     */
    private function getPatientSourceId($appointmentSourceId, $patientSourceId)
    {
        if ($appointmentSourceId !== 0 && $appointmentSourceId !== null) {
            return $appointmentSourceId;
        }

        return $patientSourceId;
    }
}
