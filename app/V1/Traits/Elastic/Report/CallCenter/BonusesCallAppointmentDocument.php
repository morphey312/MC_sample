<?php

namespace App\V1\Traits\Elastic\Report\CallCenter;

use App\V1\Facades\ElasticsearchClient;

trait BonusesCallAppointmentDocument 
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
        return [
            'id' => $this->getModelId($model),
            'date' => $this->convertDate($model->date),
            'clinic_id' => $model->clinic_id,
            'operator_id' => $model->operator_id,
            'operator_name' => $model->operator_name,
            'for_calls' => $model->for_calls,
            'for_appointments' => $model->for_appointments,
            'for_repeated' => $model->for_repeated,
            'for_incomes' => $model->for_incomes,
            'position_id' => $model->position_id,
        ];
    }

    /**
     * 
     * 
     * @return string
     */
    protected function getModelId($model)
    {
        if ($model->for_calls) {
            return static::PREFIX_CALL . $model->id; //call
        }

        if ($model->for_appointments) {
            return static::PREFIX_APPOINTMENT_FIRST . $model->id; //appointment which is treatment
        }

        if ($model->for_repeated) {
            return static::PREFIX_APPOINTMENT_REPEATED . $model->id; //call
        }

        if ($model->for_incomes) {
            return static::PREFIX_INCOME . $model->id; //appointment which is income
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
}