<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class ActionLogPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'action-logs';

    /**
     * @var array
     */
    protected $providedBy = [
        'action-logs.access' => [
            'services.update-pc-only'
        ]
    ];
}
