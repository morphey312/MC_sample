<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface EmployeeRepository extends BaseRepository
{
    const ENQUIRY_STATS_START = '08:00:00';
    
    /**
     * Find employee by SIP number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return Employee
     */ 
    public function findBySipNumber($number, $checkAccess = true);
    
    /**
     * Find employee by phone number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return Employee
     */ 
    public function findByPhoneNumber($number, $checkAccess = true);
    
    /**
     * Find employees by SIP number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return array
     */ 
    public function findAllBySipNumber($number, $checkAccess = true);
    
    /**
     * Find employees by phone number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return array
     */ 
    public function findAllByPhoneNumber($number, $checkAccess = true);
}