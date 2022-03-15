<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class AmbulanceCallPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'ambulance-calls';
}
