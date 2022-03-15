<?php

namespace App\V1\Contracts\Services;

use App\V1\Contracts\Services\Permissions\RoleHolder;

interface PermissionsService
{
    /**
     * Check if user has particular permission
     * 
     * @param User $user
     * @param string $permission
     * 
     * @return bool
     */ 
    public function has(RoleHolder $user, $permission);
    
    /**
     * Get list of user permissions
     * 
     * @param User $user
     * 
     * @return array
     */
    public function get(RoleHolder $user);

    /**
     * Check if user has any of listed permissions
     *
     * @param RoleHolder $user
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAny(RoleHolder $user, Array $permissions): bool;
}
