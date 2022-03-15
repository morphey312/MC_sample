<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface DiagnosisFilter extends Filter
{
	/**
     * Filter by search query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
	public function filterQuery($query, $value);
}