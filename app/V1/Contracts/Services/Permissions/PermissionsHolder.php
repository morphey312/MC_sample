<?php

namespace App\V1\Contracts\Services\Permissions;

interface PermissionsHolder
{
    /**
     * Get list of associated permissions
     * 
     * @return array
     */ 
    public function getPermissions();
}