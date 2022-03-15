<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;

class PrepaymentPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'prepayments';

    /**
     * @var array
     */
    protected $providedBy = [
        'prepayments.access' => [
            'payments.access',
            'payments.access-clinic',
        ],
        'prepayments.create' => [
            'payments.create',
            'payments.create-clinic',
        ],
        'prepayments.update' => [
            'payments.update',
            'payments.update-clinic',
        ],
    ];
}
