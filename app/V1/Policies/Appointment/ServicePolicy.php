<?php

namespace App\V1\Policies\Appointment;

use App\V1\Policies\BasePolicy;
use Permissions;
use App\V1\Models\User;

class ServicePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'appointment-services';

    /**
     * @var array
     */
    protected $providedBy = [
        'appointment-services.access' => [
            'patient-cabinet.access',
        ],
        'appointment-services.create' => [
            'doctor-cabinet.assign-medicine',
            'doctor-cabinet.assign-medicine',
        ],
        'appointment-services.update' => [
            'doctor-cabinet.assign-medicine',
        ],
    ];

    /**
     * Check if user can update an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function updateService(User $user)
    {
        return Permissions::has($user, 'appointments.update-service');
    }

    /**
     * Check if user can get insurance policies services
     *
     * @param User $user
     *
     * @return bool
     */
    public function actList(User $user)
    {
        return Permissions::hasAny($user, 
            ['insurance-acts.access','insurance-acts.access-clinic']);
    }
}
