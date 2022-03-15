<?php

namespace App\V1\Traits\Repositories\Query;

trait CreatedAtSorter
{
    /**
     * Apply order by creation time 
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortCreatedAt($query, $order)
    {
        $query->orderBy($query->qualifyColumn('created_at'), $order);
    }
}