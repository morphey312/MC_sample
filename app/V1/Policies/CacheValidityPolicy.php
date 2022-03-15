<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class CacheValidityPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'cache-validity';
}
