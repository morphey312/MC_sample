<?php

namespace App\V1\Contracts\Repositories\Analysis;

use App\V1\Contracts\Repositories\BaseRepository;

interface ResultRepository extends BaseRepository
{
    /**
     * Get list of years when results took place
     *
     * @param ResultFilter $filter
     * @param string $dateOf
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getYears($filter, $dateOf = 'analysis_results.date_pass');
}