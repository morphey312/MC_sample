<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface EmployeeFilter extends Filter
{
    /**
    * Filter by clinic
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterClinic($query, $value);

    /**
    * Filter by status
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterStatus($query, $value);

    /**
    * Filter by specialization
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterSpecialization($query, $value);

    /**
    * Filter by has specialization
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterHasSpecializations($query, $value);

    /**
    * Filter by full_name
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterFullName($query, $value);

    /**
    * Filter by phone
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterPhone($query, $value);

    /**
    * Filter by position
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterPositionType($query, $value);

    /**
    * Filter by doctor appointment start
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterAppointmentStart($query, $value);

    /**
    * Filter by doctor appointment end
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterAppointmentEnd($query, $value);

    /**
    *    Filter by position id
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterPosition($query, $value);

    /**
    * Filter by sip number
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterSipNumber($query, $value);

    /**
     * Filter by has voip
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterHasVoip($query, $value);
}
