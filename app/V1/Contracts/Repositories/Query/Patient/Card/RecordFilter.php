<?php

namespace App\V1\Contracts\Repositories\Query\Patient\Card;

use App\V1\Contracts\Repositories\Query\Filter;

interface RecordFilter extends Filter
{
    /** 
    * Filter appointment
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterAppointment($query, $value);

    /** 
     * Filter type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
    */
    public function filterType($query, $value);
}