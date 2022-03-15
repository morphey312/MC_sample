<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface AnalysisFilter extends Filter
{
    /**
     * Filter by name
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterName($query, $value);

    /** 
     * Filter by clinic
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinic($query, $value);
    
    /** 
     * Filter by disabled attribute
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterDisabled($query, $value);

    /** 
     * Filter by price existing
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterHasPrice($query, $value);

    /** 
     * Filter by price not existing
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterHasNoPrice($query, $value);

    /** 
     * Filter by clinic code
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterClinicCode($query, $value);

    /** 
     * Filter by laboratory code
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterLaboratoryCode($query, $value);

    /** 
     * Filter by laboratory
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterLaboratory($query, $value);
}