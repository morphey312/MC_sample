<?php

namespace App\V1\Contracts\Repositories\Query\Clinic;

use App\V1\Contracts\Repositories\Query\Scopes;

interface GroupScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}