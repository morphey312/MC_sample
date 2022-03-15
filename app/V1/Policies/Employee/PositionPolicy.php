<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\ClinicSharedPolicy;

class PositionPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */
    protected $module = 'positions';
}
