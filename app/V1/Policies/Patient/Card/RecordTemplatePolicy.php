<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class RecordTemplatePolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return Permissions::has($user, 'doctor-cabinet.access')
            || Permissions::has($user, 'patient-cabinet');
    }
}
