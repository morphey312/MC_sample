<?php

namespace App\V1\Contracts\Repositories\Query\Call;

use App\V1\Contracts\Repositories\Query\Scopes;

interface ProcessLogScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param mixed $object
     */ 
    public function scopeDefault($object);
    
    /**
     * Apply call details scope
     * 
     * @param mixed $object
     */ 
    public function scopeCall($object);
    
    /**
     * Apply actions scope
     * 
     * @param mixed $object
     */ 
    public function scopeActions($object);
}