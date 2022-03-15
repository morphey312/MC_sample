<?php

namespace App\V1\Policies;

use App\V1\Models\User;

class CallPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */ 
    protected $module = 'calls';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'calls.access' => [
            'patient-cabinet.calls-appointments',
        ],
    ];
}
