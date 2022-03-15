<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface PatientRepository extends BaseRepository
{
    const ATTR_FIRSTNAME = 'firstname';
    const ATTR_LASTNAME = 'lastname';
    const ATTR_MIDDLENAME = 'middlename';
    const ATTR_PRIMARY_PHONE = 'primary_phone_number';
    const ATTR_SECONDARY_PHONE = 'secondary_phone_number';
    const ATTR_EMAIL = 'email';
    const ATTR_STATUS = 'status';
    const ATTR_LOCATION = 'location';
    const ATTR_SOURCE = 'source';
    const ATTR_BIRTHDAY = 'birthday';
    
    /**
     * Find patient by its phone number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return Patient
     */ 
    public function findByPhoneNumber($number, $checkAccess = true);
    
    /**
     * Find patients by the phone number
     * 
     * @param string $number
     * @param bool $checkAccess
     * 
     * @return array
     */ 
    public function findAllByPhoneNumber($number, $checkAccess = true);
}