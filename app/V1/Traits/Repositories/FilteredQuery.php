<?php

namespace App\V1\Traits\Repositories;

trait FilteredQuery
{
    /**
     * Get query with filters
     * 
     * @param array $filters
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredQuery($filters = [])
    {
        $filter = $this->filter($filters);
        return $this->setupQuery(
            $this->query(),
            $filter
        );
    }
}