<?php

namespace App\V1\Policies;

class CallRequestPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */ 
    protected $module = 'call-requests';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'call-requests.create-clinic' => [
            'doctor-cabinet.schedule-visit',
        ],
    ];
}
