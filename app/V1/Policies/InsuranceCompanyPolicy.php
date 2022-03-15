<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class InsuranceCompanyPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'insurance';
}
