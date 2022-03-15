<?php

namespace App\V1\Traits\Repositories\Query;

trait DisabledFilter
{
    /** 
     * Filter to not show disabled records
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterDisabled($query, $value)
    {
        if ($this->isTrue($value)) {
            $query->where($query->qualifyColumn('disabled'), 1);
        } elseif ($this->isFalse($value)) {
            $query->where($query->qualifyColumn('disabled'), 0);
        }
    }
}