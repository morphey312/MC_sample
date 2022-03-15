<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class NextVisitPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canCreate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.schedule-visit') ;
    }

    /**
     * @inherit
     */
    protected function canUpdate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.schedule-visit') ;
    }

    /**
     * @inherit
     */
    protected function canDelete($user)
    {
        return Permissions::has($user, 'doctor-cabinet.schedule-visit') ;
    }
}
