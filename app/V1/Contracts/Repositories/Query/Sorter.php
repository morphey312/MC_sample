<?php

namespace App\V1\Contracts\Repositories\Query;

interface Sorter
{
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';
    
    /**
     * Apply this sorter to the query
     * 
     * @param mixed $queryBuilder
     */ 
    public function apply($queryBuilder);
}