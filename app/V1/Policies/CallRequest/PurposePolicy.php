<?php

namespace App\V1\Policies\CallRequest;

use App\V1\Policies\BasePolicy;

class PurposePolicy extends BasePolicy
{
    /**
     * @var string
     */
    protected $module = 'call-request-purposes';

    /**
     * @var array
     */
    protected $providedBy = [
        'call-request-purposes.access' => [
            'doctor-cabinet.schedule-visit',
        ],
    ];
}
