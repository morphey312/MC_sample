<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;
use Permissions;

class AssignedMedicinePolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return Permissions::has($user, 'doctor-cabinet.assign-medicine');
    }

    /**
     * @inherit
     */ 
    protected function canCreate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.assign-medicine');
    }

    /**
     * @inherit
     */ 
    protected function canDelete($user)
    {
        return Permissions::has($user, 'doctor-cabinet.assign-medicine');
    }
}
