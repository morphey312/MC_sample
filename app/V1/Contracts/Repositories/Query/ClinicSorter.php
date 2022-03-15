<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Sorter;

interface ClinicSorter extends Sorter
{
    /**
    * Apply order by name
    * 
    * @param mixed $queryBuilder
    * @param string $order
    */ 
    public function sortName($queryBuilder, $order);

    /**
    * Apply order by status
    * 
    * @param mixed $queryBuilder
    * @param string $order
    */ 
    public function sortStatus($queryBuilder, $order);
}