<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;
use Permissions;

class AssignedServicePolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canDelete($user)
    {
        return Permissions::has($user, 'doctor-cabinet.diagnostic')
            || Permissions::has($user, 'doctor-cabinet.assign-procedure')
            || Permissions::has($user, 'doctor-cabinet.assign-therapy');
    }
}
