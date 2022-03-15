<?php

namespace App\V1\Contracts\Repositories\Query\Appointment;

use App\V1\Contracts\Repositories\Query\Scopes;

interface StatusScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}