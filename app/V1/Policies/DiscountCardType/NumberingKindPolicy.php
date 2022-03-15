<?php

namespace App\V1\Policies\DiscountCardType;

use App\V1\Policies\BasePolicy;
use App\V1\Policies\ClinicSharedPolicy;

class NumberingKindPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'card-numbering-kinds';
}
