<?php

namespace App\V1\Traits\Repositories\Query;


trait OperatorNameSorter
{
    /**
     * Apply order by operator name
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortOperatorName($query, $order)
    {
        $query->orderByJoin('employees.last_name', $order, 'operator_id');
        $query->orderByJoin('employees.first_name', $order, 'operator_id');
        $query->orderByJoin('employees.middle_name', $order, 'operator_id');
    }
}