<?php

namespace App\V1\Traits\Repositories\Query;

trait ClinicsFilter
{
    /** 
     * Filter by clinic
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinic($query, $value)
    {
        $query->whereHas('clinics', function($query) use($value) {
            $query->whereIn($query->qualifyColumn('id'), $this->safeArray($value));
        });
    }

    /** 
     * Filter by clinics from same group 
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinicSameGroup($query, $value)
    {
        $query->whereHas('clinics', function($query) use($value) {
            $ids = $this->safeArray($value);
            $query->leftJoin('clinics AS siblings', function($join) use($query) {
                    $join->on($query->qualifyColumn('id'), '!=', 'siblings.id')
                        ->on($query->qualifyColumn('group_id'), '=', 'siblings.group_id');
                })
                ->whereIn($query->qualifyColumn('id'), $ids)
                ->orWhereIn('siblings.id', $ids);
        });
    }
}