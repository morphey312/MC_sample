<?php

namespace App\V1\Contracts\Services\Permissions;

interface SharedResource
{
    /**
     * Get ID of the user who owns this entity
     * 
     * @return int
     */ 
    public function getUserId();
    
    /**
     * Get ID of the company that owns this entity
     * 
     * @return int
     */ 
    public function getCompanyId();
    
    /**
     * Check is user is owner of this resource
     * 
     * @param ResourceHolder $user
     * 
     * @return bool
     */ 
    public function isOwnedBy(ResourceHolder $user);
    
    /**
     * Check is user can access this resource
     * 
     * @param ResourceHolder $user
     * 
     * @return bool
     */ 
    public function isAccessibleBy(ResourceHolder $user);
    
    /**
     * Apply filter to query to select only accessible resources
     * 
     * @param mixed $builder
     * @param ResourceHolder $user
     */
    public function applyAccessFilter($builder, ResourceHolder $user);
}