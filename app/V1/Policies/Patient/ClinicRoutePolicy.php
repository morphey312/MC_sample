<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;
use Permissions;

class ClinicRoutePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-clinic-routes';

    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return Permissions::has($user, 'patient-clinic-routes.access')
            || Permissions::has($user, 'patient-clinic-routes.view');
    }
}
