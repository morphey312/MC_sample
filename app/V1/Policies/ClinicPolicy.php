<?php

namespace App\V1\Policies;

class ClinicPolicy extends BasePolicy
{
    /**
     * @var string
     */
    protected $module = 'clinics';

    /**
     * @var array
     */
    protected $providedBy = [
        'clinics.access' => [
            'acts-and-payments.view-acts-and-payments',
        ]
    ];
}
