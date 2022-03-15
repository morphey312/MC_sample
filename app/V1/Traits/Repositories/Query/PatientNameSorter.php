<?php

namespace App\V1\Traits\Repositories\Query;


trait PatientNameSorter
{
    /**
     * Apply order by patient name
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortPatientName($query, $order)
    {
        $query->withJoined('patients', 'patient_id', function($query, $alias) use($order) {
            $query->orderBy($alias . '.lastname', $order);
            $query->orderBy($alias . '.firstname', $order);
            $query->orderBy($alias . '.middlename', $order);
        });
    }
}