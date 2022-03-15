<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Filter;

interface PatientFilter extends Filter
{
    /**
     * Filter by last name
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterLastname($query, $value);

    /**
     * Filter by first name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterFirstname($query, $value);

    /**
     * Filter by middle name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterMiddlename($query, $value);

    /**
     * Filter by phone number
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterPhone($query, $value);

    /**
     * Filter by status
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterStatus($query, $value);

    /**
     * Filter by clinic
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterClinic($query, $value);

    /**
     * Filter by email
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterEmail($query, $value);

    /**
     * Filter by location
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterLocation($query, $value);

    /**
     * Filter by source
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */ 
    public function filterSource($query, $value);

    /**
     * Filter by firstname, middlename, lastname
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterFullName($query, $value);

    /**
     * Filter by birthday from date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterBirthdayFrom($query, $value);

    /**
     * Filter by birthday to date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterBirthdayTo($query, $value);

    /**
     * Filter by card clinic
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterCardClinic($query, $value);

    /**
     * Filter by card specialization
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterCardSpecialization($query, $value);

    /**
     * Filter by card specialization
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterCardNumber($query, $value);
}