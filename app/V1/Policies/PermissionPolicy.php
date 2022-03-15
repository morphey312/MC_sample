<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class PermissionPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'roles';
}
