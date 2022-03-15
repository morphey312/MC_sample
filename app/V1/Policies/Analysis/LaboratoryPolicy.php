<?php

namespace App\V1\Policies\Analysis;

use App\V1\Policies\BasePolicy;
use App\V1\Policies\ClinicSharedPolicy;

class LaboratoryPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'laboratories';
}
