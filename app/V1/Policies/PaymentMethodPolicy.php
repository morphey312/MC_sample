<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class PaymentMethodPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'payment-methods';
}
