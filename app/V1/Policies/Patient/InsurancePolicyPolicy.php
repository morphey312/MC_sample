<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;

class InsurancePolicyPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'insurance-policies';
}
