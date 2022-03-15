<?php

namespace App\V1\Contracts\Repositories\Query\Patient\Card;

use App\V1\Contracts\Repositories\Query\Scopes;

interface AssignmentScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}