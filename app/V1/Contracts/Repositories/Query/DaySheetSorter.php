<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Sorter;

interface DaySheetSorter extends Sorter
{
    /**
    * Apply order by date
    * 
    * @param mixed $queryBuilder
    * @param string $order
    */ 
    public function sortDate($queryBuilder, $order);
}