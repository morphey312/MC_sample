<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\BasePolicy;

class OperatorBonusPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'operator-bonuses';
}
