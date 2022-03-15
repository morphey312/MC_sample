<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\ClinicSharedPolicy;

class OutclinicDiagnosticPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'outclinic-diagnostic';

    /**
     * @var array
     */
    protected $providedBy = [
        'outclinic-diagnostic.update' => [
            'doctor-cabinet.start-course'
        ],
    ];
}
