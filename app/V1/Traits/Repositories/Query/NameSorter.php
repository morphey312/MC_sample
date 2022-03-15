<?php

namespace App\V1\Traits\Repositories\Query;

trait NameSorter
{
    /**
     * Apply order by name
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortName($query, $order)
    {
        $query->orderBy($query->qualifyColumn('name'), $order);
    }

    /**
     * Apply order by name_lc1
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortNameLc1($query, $order)
    {
        $query->orderBy($query->qualifyColumn('name_lc1'), $order);
    }

    /**
     * Apply order by name_i18n
     * 
     * @param mixed $queryBuilder
     * @param string $order
     */ 
    public function sortNameI18n($query, $order)
    {
        $this->sortI18NAttribute($query, 'name', $order);
    }
}