<?php

namespace App\V1\Policies;

use App\V1\Models\User;

class AppointmentPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */ 
    protected $module = 'appointments';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'appointments.access' => [
            'patient-cabinet.calls-appointments',
        ],
        'appointments.access-clinic' => [
            'doctor-cabinet.access',
        ],
        'appointments.update-clinic' => [
            'doctor-cabinet.access',
        ],
        'appointments.update-service' => [
            'doctor-cabinet.access',
        ],
    ];
}
