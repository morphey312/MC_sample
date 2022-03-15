<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class CountryPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'countries';
}
