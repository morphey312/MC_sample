<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface LockLogFilter extends Filter
{
    /**
    * Filter by employee
    *
    * @param \Illuminate\Database\Eloquent\Builder $query
    * @param $value
    */
    public function filterEmployee($query, $value);
}
