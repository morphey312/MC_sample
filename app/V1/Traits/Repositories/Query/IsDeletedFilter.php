<?php

namespace App\V1\Traits\Repositories\Query;

trait IsDeletedFilter
{
    /** 
     * Filter cashier
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterIsDeleted($query, $value)
    {
        if ($this->isTrue($value)) {
            $query->where($query->qualifyColumn('is_deleted'), '=', 1);
        } elseif ($this->isFalse($value)) {
            $query->where($query->qualifyColumn('is_deleted'), '=', 0);
        }
    }
}