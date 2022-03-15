<?php

namespace App\V1\Traits\Repositories\Query;

trait ClinicIdFilter
{
    /**
     * Filter by clinic
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinic($query, $value)
    {
        $query->whereIn($query->qualifyColumn('clinic_id'), $this->safeArray($value));
    }

    /**
     * Filter by clinics from same group
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinicSameGroup($query, $value)
    {
        $query->whereIn($query->qualifyColumn('clinic_id'), function($query) use($value) {
            $ids = $this->safeArray($value);
            $query->select('clinics.id')
                ->from('clinics')
                ->leftJoin('clinics AS siblings', function($join) {
                    $join->on('clinics.id', '!=', 'siblings.id')
                        ->on('clinics.group_id', '=', 'siblings.group_id');
                })
                ->whereIn('clinics.id', $ids)
                ->orWhereIn('siblings.id', $ids);
        });
    }
}
