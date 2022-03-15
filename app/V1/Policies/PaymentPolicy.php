<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class PaymentPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'payments';

    /**
     * @var array
     */
    protected $providedBy = [
        'payments.access' => [
            'patient-cabinet.payments',
        ],
    ];
}
