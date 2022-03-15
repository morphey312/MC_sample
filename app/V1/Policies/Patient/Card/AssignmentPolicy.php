<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use App\V1\Models\User;
use Permissions;

class AssignmentPolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function can(User $user, $action)
    {
        return Permissions::has($user, 'doctor-cabinet.assign-analyses') 
            || Permissions::has($user, 'doctor-cabinet.diagnostic') 
            || Permissions::has($user, 'doctor-cabinet.assign-medicine')
            || Permissions::has($user, 'doctor-cabinet.assign-procedure')
            || Permissions::has($user, 'doctor-cabinet.assign-therapy');
    }
}
