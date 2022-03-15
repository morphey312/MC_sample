<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\BasePolicy;

class DoctorIncomePlanPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'doctor-income-plans';
}
