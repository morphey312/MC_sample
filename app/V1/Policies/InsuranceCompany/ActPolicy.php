<?php

namespace App\V1\Policies\InsuranceCompany;

use App\V1\Policies\BasePolicy;

class ActPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'insurance-acts';
    protected $providedBy = [
        'insurance-acts.update' => [
            'insurance-acts.create',
        ],
        'insurance-acts.access' => [
            'insurance-acts.access-clinic',
        ],
    ];
}
