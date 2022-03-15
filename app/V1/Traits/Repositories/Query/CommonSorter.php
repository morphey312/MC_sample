<?php

namespace App\V1\Traits\Repositories\Query;

trait CommonSorter
{
    use ClinicNameSorter,
        SpecializationNameSorter,
        DoctorNameSorter;
    
    /**
     * Sort by patient lastname
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $order
     */
    public function sortPatientLastName($query, $order)
    {
        $query->orderByJoin('patients.lastname', $order, 'patient_id');
    }
}