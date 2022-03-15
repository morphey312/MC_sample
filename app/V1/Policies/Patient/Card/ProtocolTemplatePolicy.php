<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\ClinicSharedPolicy;

class ProtocolTemplatePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'protocol-templates';
}
