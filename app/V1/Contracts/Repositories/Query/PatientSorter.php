<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Sorter;

interface PatientSorter extends Sorter
{
    /**
     * Apply order by date time
     * 
     * @param mixed $queryBuilder
     * @param string $order
    */ 
    public function sortFullName($queryBuilder, $order);
}