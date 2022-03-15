<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class RecordPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canAccess($user)
    {
        return Permissions::has($user, 'doctor-cabinet.access')
            || Permissions::has($user, 'patient-cabinet.history')
            || Permissions::has($user, 'patient-cabinet.documents');
    }
}
