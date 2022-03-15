<?php

namespace App\V1\Contracts\Repositories\Query\Call;

use App\V1\Contracts\Repositories\Query\Filter;

interface DeleteReasonFilter extends Filter
{
    /**
     * Filter by use for delete
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterUseForDelete($query, $value);
}