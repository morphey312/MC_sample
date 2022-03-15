<?php

namespace App\V1\Policies\PriceAgreementAct;

use App\V1\Policies\BasePolicy;

class PricePolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'price-agreement-acts';

    /**
     * @var array
     */
    protected $providedBy = [
        'price-agreement-acts.access' => [
            'price-agreement-acts.access',
            'price-agreement-acts.access-clinic',
        ],
        'price-agreement-acts.delete' => [
            'price-agreement-acts.delete-prices'
        ],
        'price-agreement-acts.update' => [
            'price-agreement-acts.update',
            'price-agreement-acts.update-clinic',
        ],
    ];
}
