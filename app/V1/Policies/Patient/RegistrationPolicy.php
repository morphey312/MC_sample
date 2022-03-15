<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;

class RegistrationPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-registrations';
}
