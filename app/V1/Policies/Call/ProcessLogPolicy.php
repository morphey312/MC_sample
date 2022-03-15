<?php

namespace App\V1\Policies\Call;

use App\V1\Policies\ClinicSharedPolicy;

class ProcessLogPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'process-logs';
}
