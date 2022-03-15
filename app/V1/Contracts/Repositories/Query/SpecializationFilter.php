<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface SpecializationFilter extends Filter
{
    /** 
     * Filter clinic
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinic($query, $value);

    /** 
     * Filter employee
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterEmployee($query, $value);

    /** 
     * Filter not_use_for_new_patient_call
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterNotUseForCall($query, $value);
}