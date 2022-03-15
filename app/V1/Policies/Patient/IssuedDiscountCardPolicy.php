<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;

class IssuedDiscountCardPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-discount-cards';
}
