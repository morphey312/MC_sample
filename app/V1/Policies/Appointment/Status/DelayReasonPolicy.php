<?php

namespace App\V1\Policies\Appointment\Status;

use App\V1\Policies\BasePolicy;

class DelayReasonPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'appointment-status-delay-reasons';
}
