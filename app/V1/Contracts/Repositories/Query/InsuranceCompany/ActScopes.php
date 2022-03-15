<?php

namespace App\V1\Contracts\Repositories\Query\InsuranceCompany;

use App\V1\Contracts\Repositories\Query\Scopes;

interface ActScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}