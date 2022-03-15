<?php

namespace App\V1\Contracts\Services\Permissions;

interface ResourceHolder
{
    /**
     * Get ID of the user
     * 
     * @return int
     */ 
    public function getUserId();
    
    /**
     * Get ID of the user's company
     * 
     * @return int
     */ 
    public function getCompanyId();
    
    /**
     * Check is user is owner of this resource
     * 
     * @param SharedResource $resource
     * 
     * @return bool
     */ 
    public function isOwnerOf(SharedResource $resource);
    
    /**
     * Check is user can access this resource
     * 
     * @param SharedResource $resource
     * 
     * @return bool
     */ 
    public function canAccess(SharedResource $resource);
}