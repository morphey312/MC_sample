<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\BasePolicy;

class ServiceTypePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'employee-service-types';
}
