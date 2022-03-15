<?php

namespace App\V1\Policies\Ehealth;

use App\V1\Policies\BasePolicy;

class ApplicationPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'applications';

    /**
     * @var array
     */
    protected $providedBy = [
        'applications.access' => [
            'ehealth.application-history',
        ],
    ];
}
