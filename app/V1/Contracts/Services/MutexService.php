<?php

namespace App\V1\Contracts\Services;

interface MutexService
{
    /**
     * Try lock the resource
     * 
     * @param mixed $resource
     * @param int|bool $expires
     * 
     * @return bool 
     */ 
    public function lock($resource, $expires = false);

    /**
     * Unlock the resource
     * 
     * @param mixed $resource
     * 
     * @return bool
     */ 
    public function unlock($resource);

    /**
     * Check if resource is locked
     * 
     * @return bool
     */
    public function isLocked($resource);
}