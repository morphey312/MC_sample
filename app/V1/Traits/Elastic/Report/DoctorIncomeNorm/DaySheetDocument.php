<?php

namespace App\V1\Traits\Elastic\Report\DoctorIncomeNorm;

use App\V1\Facades\ElasticsearchClient;
use App\V1\Models\Appointment;
use App\V1\Models\DaySheet;
use App\V1\Models\Employee;

trait DaySheetDocument
{
    /**
     * Get model query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return Appointment::query();
    }

    /**
     * Get document
     * 
     * @param mixed $appointment
     * 
     * @return array
     */
    protected function getDocument($row)
    {
        return [
            'clinic_id' => $row->clinic_id,
            'doctor_id' => $row->doctor_id,
            'date' => $row->date,
            'id' => $row->id,
            'name' => $row->name,
        ];
    }

    /**
     * Create index if not exists
     */
    protected function verifyIndexExists()
    {
        if (!ElasticsearchClient::indexExists(DaySheet::REPORT_DOCTOR_DAYSHEET_INDEX_NAME)) {
            ElasticsearchClient::createIndex(DaySheet::REPORT_DOCTOR_DAYSHEET_INDEX_NAME);
        }
    }
}