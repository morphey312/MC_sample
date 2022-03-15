<?php

namespace App\V1\Traits\Repositories\Query;

trait SearchQueryI18nFilter
{
    /**
     * Filter by search query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterQuery($query, $value)
    {
        $this->filterI18NAttribute($query, 'name', $value);
    }
}