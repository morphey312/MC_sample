<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\ClinicSharedPolicy;

class OutclinicSpecializationPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'outclinic-specialization';

    /**
     * @var array
     */
    protected $providedBy = [
        'outclinic-specialization.update' => [
            'doctor-cabinet.start-course'
        ],
    ];
}
