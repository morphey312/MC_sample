<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;

class UserPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-users';
}
