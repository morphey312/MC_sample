<?php

namespace App\V1\Traits\Repositories\Query;

trait SpecializationFilter
{
    /** 
     * Filter by specialization
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterSpecialization($query, $value)
    {
        $query->whereIn($query->qualifyColumn('specialization_id'), $this->safeArray($value));
    }
}