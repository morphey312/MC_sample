<?php

namespace App\V1\Traits\Repositories\Query;

trait NameFilter
{
    /**
     * Filter by name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterName($query, $value)
    {
        $this->filterAttribute($query, $query->qualifyColumn('name'), $value);
    }

    /**
     * Filter by name_lc1
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterNameLc1($query, $value)
    {
        $this->filterAttribute($query, $query->qualifyColumn('name_lc1'), $value);
    }

    /**
     * Filter by localized name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterNameI18n($query, $value)
    {
        $this->filterI18NAttribute($query, 'name', $value);
    }
}