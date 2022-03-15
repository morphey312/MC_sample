<?php

namespace App\V1\Contracts\Repositories\Appointment;

use App\V1\Contracts\Repositories\BaseRepository;

interface ServiceRepository extends BaseRepository
{
    /**
     * Get service balances not debt
     *
     * @param DebtFilter $filter
     * @param $callback
     * @param array $statuses
     *
     * @return @mixed
     */
    public function getServicesBalance($filter, $callback, $statuses = []);

    /**
     * Get service balances Not debt
     *
     * @param DebtFilter $filter
     * @param $callback
     * @param array $statuses
     *
     * @return @mixed
     */
    public function getServicesBalanceNotDebt($filter, $callback, $statuses = []);
}