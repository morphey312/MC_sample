<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface CallRequestFilter extends Filter
{
    /**
    * Filter by purpose
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterPurpose($query, $value);

    /**
    * Filter by status
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterStatus($query, $value);

    /**
    * Filter by created_at from date
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterCreatedStart($query, $value);

    /**
    * Filter by created_at to date
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterCreatedEnd($query, $value);

    /**
    * Filter by recall_from
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterRecallPeriodStart($query, $value);

    /**
    * Filter by recall_to
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterRecallPeriodEnd($query, $value);

    /**
    * Filter by appointment date from filter date
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterAppointmentDateStart($query, $value);

    /**
    * Filter by appointment date to filter date
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterAppointmentDateEnd($query, $value);
}