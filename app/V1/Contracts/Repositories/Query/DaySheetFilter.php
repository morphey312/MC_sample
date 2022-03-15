<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface DaySheetFilter extends Filter
{
    /** 
    * Filter by employee clinic
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterClinic($query, $value);

    /** 
    * Filter by specializations
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterSpecialization($query, $value);

    /** 
    * Filter by date
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDate($query, $value);

    /** 
    * Filter by date start
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDateStart($query, $value);

    /** 
    * Filter by date end
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterDateEnd($query, $value);

    /** 
    * Filter by employee
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterEmployees($query, $value);
}