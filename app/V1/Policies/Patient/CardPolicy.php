<?php

namespace App\V1\Policies\Patient;

use App\V1\Policies\BasePolicy;
use App\V1\Models\Patient\Card;
use App\V1\Models\User;

class CardPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-cards';
}
