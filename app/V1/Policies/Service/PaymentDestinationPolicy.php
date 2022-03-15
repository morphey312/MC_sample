<?php

namespace App\V1\Policies\Service;

use App\V1\Policies\BasePolicy;

class PaymentDestinationPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'service-payment-destinations';
}
