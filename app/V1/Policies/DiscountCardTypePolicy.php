<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class DiscountCardTypePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'discount-card-types';
}
