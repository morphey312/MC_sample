<?php

namespace App\V1\Contracts\Repositories\Query\Employee;

use App\V1\Contracts\Repositories\Query\Scopes;

interface DocumentScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}