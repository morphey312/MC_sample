<?php

namespace App\V1\Contracts\Services\Voip;

interface SubResolver
{
    /**
     * Resolve subscriber to existing entity
     * 
     * @param string $sub
     * 
     * @return array
     */ 
    public function resolve($sub);
    
    /**
     * Resolve all subscribers
     * 
     * @param string $sub
     * 
     * @return array
     */ 
    public function resolveAll($sub);
}