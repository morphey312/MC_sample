<?php

namespace App\V1\Policies\Clinic;

use App\V1\Policies\BasePolicy;
use App\V1\Policies\ClinicSharedPolicy;

class MoneyRecieverPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'money-recievers';

    /**
     * @var array
     */
    protected $providedBy = [
        'money-recievers.access' => [
            'acts-and-payments.shape-acts',
            'acts-and-payments.shape-payments',
        ],
    ];
}
