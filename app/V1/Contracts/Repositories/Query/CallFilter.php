<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface CallFilter extends Filter
{
    /**
    * Filter by clinic
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param mixed $value
    */
    public function filterClinic($query, $value);

    /** 
    * Filter by specialization
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterSpecialization($query, $value);

    /** 
    * Filter by operator
    * 
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterOperator($query, $value);

    /**
    * Filter by date from
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
    * Filter by is call deleted
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterIsDeleted($query, $value);
}