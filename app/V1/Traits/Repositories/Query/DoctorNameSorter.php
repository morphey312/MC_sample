<?php

namespace App\V1\Traits\Repositories\Query;


trait DoctorNameSorter
{
    /**
     * Apply order by doctor name
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $order
     */ 
    public function sortDoctorName($query, $order)
    {
        $query->orderByJoin('employees.last_name', $order, 'doctor_id');
        $query->orderByJoin('employees.first_name', $order, 'doctor_id');
        $query->orderByJoin('employees.middle_name', $order, 'doctor_id');
    }
}