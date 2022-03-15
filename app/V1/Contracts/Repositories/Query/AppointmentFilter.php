<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface AppointmentFilter extends Filter
{
    /** 
    * Filter clinic
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterClinic($query, $value);

    /** 
    * Filter specialization
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterSpecialization($query, $value);

    /** 
    * Filter doctor
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDoctor($query, $value);

    /** 
    * Filter date start
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDateStart($query, $value);

    /** 
    * Filter date end
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDateEnd($query, $value);
}