<?php

namespace App\V1\Traits\Elastic\Report\CallCenter;

use App\V1\Facades\ElasticsearchClient;
use App\V1\Models\SessionLog;

trait SessionLogDocument 
{
    /**
     * Get model query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return SessionLog::query();
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
            'id' => $model->date . '-' . $model->operator_id,
            'date' => $model->date,
            'duration' => (int)$model->duration,
            'wrapup_count' => $model->wrapup_count,
            'operator_id' => $model->operator_id,
        ];
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