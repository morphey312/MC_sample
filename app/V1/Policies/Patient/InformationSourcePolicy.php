<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\ClinicSharedPolicy;

class InformationSourcePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'information-sources';
}
