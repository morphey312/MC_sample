<?php

namespace App\V1\Traits\Repositories\Query;

trait DateFilter
{
    /**
     * Filter date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterDate($query, $value)
    {
        $query->where($query->qualifyColumn('date'), '=', $this->safeString($value));
    }

    /**
     * Filter date start
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterDateStart($query, $value)
    {
        $query->where($query->qualifyColumn('date'), '>=', $this->safeString($value));
    }

    /** Filter date end
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterDateEnd($query, $value)
    {
        $query->where($query->qualifyColumn('date'), '<=', $this->safeString($value));
    }
}
