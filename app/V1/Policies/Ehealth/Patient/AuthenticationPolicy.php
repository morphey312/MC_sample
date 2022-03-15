<?php

namespace App\V1\Policies\Ehealth\Patient;

use App\V1\Policies\BasePolicy;

class AuthenticationPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'patients';
}
