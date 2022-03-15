<?php

namespace App\V1\Policies\Appointment;

use App\V1\Policies\ClinicSharedPolicy;

class LimitationPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'limitations';
}
