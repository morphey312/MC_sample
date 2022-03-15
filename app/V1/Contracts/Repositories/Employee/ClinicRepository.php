<?php

namespace App\V1\Contracts\Repositories\Employee;

use App\V1\Contracts\Repositories\BaseRepository;

interface ClinicRepository extends BaseRepository
{
    /**
     * Get user id by SIP number
     * 
     * @param string $sip
     * 
     * @return int
     */
    public function getUserIdBySipNumber($sip);
}