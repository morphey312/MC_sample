<?php

namespace App\V1\Policies\Ehealth;

use App\V1\Models\User;
use App\V1\Policies\BasePolicy;

class PatientPolicy extends BasePolicy
{
    /**
     * @var string
     */
    protected $module = 'patients';
}
