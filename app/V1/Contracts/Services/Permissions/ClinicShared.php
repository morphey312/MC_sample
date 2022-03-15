<?php

namespace App\V1\Contracts\Services\Permissions;

interface ClinicShared
{
    /**
     * Get clinic ids
     * 
     * @return array
     */ 
    public function getClinicIds();
}