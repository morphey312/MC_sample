<?php

namespace App\V1\Policies\Appointment;

use App\V1\Policies\BasePolicy;
use Permissions;

class DelayPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'appointment-delays';

    /**
     * @var array
     */
    protected $providedBy = [
        'appointment-delays.create' => [
            'appointments.create',
            'appointments.create-clinic',
        ],
    ];

    /**
     * @inherit
     */ 
    protected function canAccess($user)
    {
        return (Permissions::has($user, 'appointment-delays.access') && Permissions::has($user, 'appointments.access'))
            || (Permissions::has($user, 'appointment-delays.access-clinic') && Permissions::has($user, 'appointments.access-clinic'));
    }
}
