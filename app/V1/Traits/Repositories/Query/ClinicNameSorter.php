<?php

namespace App\V1\Traits\Repositories\Query;


trait ClinicNameSorter
{
    /**
     * Apply order by clinic name
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $order
     */ 
    public function sortClinicName($query, $order)
    {
        $query->orderByJoin('clinics.name', $order, 'clinic_id');
    }
}