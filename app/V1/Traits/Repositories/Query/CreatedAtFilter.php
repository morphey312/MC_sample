<?php

namespace App\V1\Traits\Repositories\Query;

trait CreatedAtFilter
{
    /**
     * Filter date created
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterCreatedAt($query, $value)
    {
        $this->filterDateAttribute($query, $query->qualifyColumn('created_at'), $value);
    }
}

