<?php

namespace App\V1\Contracts\Repositories\Query;

interface Filter
{
    /**
     * Set default values for this filter
     * 
     * @param string|array $attribute
     * @param mixed $value
     */ 
    public function setDefault($attribute, $value = null);

    /**
     * Apply this filter to the query
     * 
     * @param mixed $queryBuilder
     */ 
    public function apply($queryBuilder);

    /**
     * Override filter value
     * 
     * @param string|array $attribute
     * @param mixed $value
     */ 
    public function setFilter($attribute, $value = null);

    /**
     * Unset filter
     * 
     * @param string $attribute
     */ 
    public function unsetFilter($attribute);

    /**
     * Get filter value
     * 
     * @param string|array $attribute
     * @param mixed $value
     */ 
    public function getFilter($attribute);

    /**
     * Get values of the filter
     * 
     * @return array
     */ 
    public function getFilterValues();
}