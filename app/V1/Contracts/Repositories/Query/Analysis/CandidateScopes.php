<?php

namespace App\V1\Contracts\Repositories\Query\Analysis;

use App\V1\Contracts\Repositories\Query\Scopes;

interface CandidateScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}