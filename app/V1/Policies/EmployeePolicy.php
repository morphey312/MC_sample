<?php

namespace App\V1\Policies;

class EmployeePolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */ 
    protected $module = 'employees';
}