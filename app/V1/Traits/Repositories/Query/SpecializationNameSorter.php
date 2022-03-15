<?php

namespace App\V1\Traits\Repositories\Query;


trait SpecializationNameSorter
{
    /**
     * Apply order by specialization name
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $order
     */ 
    public function sortSpecializationName($query, $order)
    {
        $query->orderByJoin('specializations.name', $order, 'specialization_id');
    }
}