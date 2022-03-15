<?php

namespace App\V1\Policies\Appointment\Status;

use App\V1\Policies\BasePolicy;

class ReasonPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'appointment-status-reasons';
}
