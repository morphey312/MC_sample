<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface AppointmentRepository extends BaseRepository
{
    /**
     * Get all patient appointmnets
     * 
     * @param int $patientId
     * @param string $date
     * @param int $clinicId
     * @param int $statusId
     * 
     * @return \Illuminate\Support\Collection
     */ 
    public function getPatientClinicAppointments($patientId, $date, $clinicId, $statusId);

    /**
     * Get list for income report
     * 
     * @param AppointmentFilter $filter
     * 
     * @return mixed
     */
    public function getReportList($filter);
}