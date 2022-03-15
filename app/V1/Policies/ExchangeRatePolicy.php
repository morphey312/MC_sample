<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;
use Permissions;

class ExchangeRatePolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return Permissions::has($user, 'payments.access-exchange');
    }
}
