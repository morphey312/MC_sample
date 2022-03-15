<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface TreatmentCourseFilter extends Filter
{
	/** 
     * Filter patient
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
	public function filterPatient($query, $value);
}