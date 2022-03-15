<?php

namespace App\V1\Traits\Repositories\Query;

trait SearchQueryFilter
{
    /**
     * Filter by search query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterQuery($query, $value)
    {
        $this->filterAttribute($query, $query->qualifyColumn('name'), $value);
    }
}