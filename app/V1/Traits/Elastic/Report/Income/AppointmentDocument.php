<?php

namespace App\V1\Traits\Elastic\Report\Income;

use App\V1\Facades\ElasticsearchClient;
use App\V1\Models\Appointment;

trait AppointmentDocument
{
    /**
     * Get model query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return Appointment::with([
            'specialization',
            'doctor',
        ]);
    }

    /**
     * Get document
     *
     * @param mixed $appointment
     *
     * @return array
     */
    protected function getDocument($appointment)
    {
        $firstAppointmentInTreatmentCourse = ($appointment->treatment_course_id !== null) ? $appointment->firstAppointmentInTreatmentCourse : null;

        return [
            'id' => $appointment->id,
            'is_first' => $appointment->is_first,
            'date' => $appointment->date,
            'clinic_id' => $appointment->clinic_id,
            'is_deleted' => $appointment->is_deleted,
            'appointment_status_id' => $appointment->appointment_status_id,
            'doctor_id' => $appointment->doctor_id,
            'doctor_type' => $appointment->doctor_type,
            'doctor_name' => $appointment->doctor->full_name,
            'workspace_doctor_id' => $appointment->workspace_doctor_id,
            'workspace_doctor_name' => ($appointment->workspace_doctor_id === null) ? null : $appointment->workspaceDoctor->full_name,
            'card_specialization_id' => $appointment->card_specialization_id,
            'specialization_id' => $appointment->specialization_id,
            'specialization_name' => $appointment->specialization->short_name,
            'is_first_in_treatment_course' => ($firstAppointmentInTreatmentCourse !== null) ? ($firstAppointmentInTreatmentCourse->id === $appointment->id) : false,
        ];
    }

    /**
     * Create index if not exists
     */
    protected function verifyIndexExists()
    {
        $indexName = $this->incomeAppointmentIndexName();
        if (!ElasticsearchClient::indexExists($indexName)) {
            ElasticsearchClient::createIndex($indexName);
        }
    }
}
