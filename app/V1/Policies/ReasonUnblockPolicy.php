<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class ReasonUnblockPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'day-sheet-time-unblock-reasons';
}
