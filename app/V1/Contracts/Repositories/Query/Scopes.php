<?php

namespace App\V1\Contracts\Repositories\Query;

interface Scopes
{
    /**
     * Apply this scope to the data
     * 
     * @param mixed $object
     */ 
    public function apply($object);

    /**
     * Set scope overrides
     * 
     * @param array $scopes
     */ 
    public function setScopes($scopes);

    /**
     * Add scopes to initial ones
     * 
     * @param array $scopes
     */ 
    public function addScopes($scopes);
}