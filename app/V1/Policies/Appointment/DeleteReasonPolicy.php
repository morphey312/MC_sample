<?php

namespace App\V1\Policies\Appointment;

use App\V1\Policies\BasePolicy;

class DeleteReasonPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'appointment-delete-reasons';
}
