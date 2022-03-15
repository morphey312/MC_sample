<?php

namespace App\V1\Contracts\Repositories\Query\LegalEntity;

use App\V1\Contracts\Repositories\Query\Scopes;

interface ClinicScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}