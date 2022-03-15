<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;
use Permissions;

class SignalRecordPolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canCreate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.signal-records');
    }
    
    /**
     * @inherit
     */ 
    protected function canUpdate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.signal-records');
    }
    
    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return Permissions::has($user, 'doctor-cabinet.access')
            || Permissions::has($user, 'patient-cabinet.access');
    }
}
