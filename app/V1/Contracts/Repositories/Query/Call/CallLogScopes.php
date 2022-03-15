<?php

namespace App\V1\Contracts\Repositories\Query\Call;

use App\V1\Contracts\Repositories\Query\Scopes;

interface CallLogScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param mixed $object
     */ 
    public function scopeDefault($object);
    
    /**
     * Apply patient scope
     * 
     * @param mixed $object
     */ 
    public function scopePatient($object);
    
    /**
     * Apply process scope
     * 
     * @param mixed $object
     */ 
    public function scopeProcess($object);
}