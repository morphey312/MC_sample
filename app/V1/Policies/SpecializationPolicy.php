<?php

namespace App\V1\Policies;

class SpecializationPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */
    protected $module = 'specializations';


     /**
     * @var array
     */
    protected $providedBy = [
        'specializations.access' => [
            'acts-and-payments.shape-acts',
            'acts-and-payments.shape-payments',
        ],
    ];
}
