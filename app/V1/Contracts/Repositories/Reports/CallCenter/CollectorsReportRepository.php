<?php

namespace App\V1\Contracts\Repositories\Reports\CallCenter;

use App\V1\Contracts\Repositories\BaseRepository;
use App\V1\Contracts\Repositories\Query\Filter;

interface CollectorsReportRepository extends BaseRepository
{
    /**
     * Get collector calls and appointments data
     * 
     * @param App\V1\Contracts\Repositories\Query\Filter $filters
     * @param array $collectors
     * 
     * @return mixed
     */
    public function getCollectorCalls(Filter $filters, $collectors = []);

    /**
     * Get collector payments data
     * 
     * @param App\V1\Contracts\Repositories\Query\Filter $filter
     * @param array $collectors
     * 
     * @return mixed
     */
    public function getCollectorPayments(Filter $filter, $collectors = []);
}
