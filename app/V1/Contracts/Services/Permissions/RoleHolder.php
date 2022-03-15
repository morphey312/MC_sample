<?php

namespace App\V1\Contracts\Services\Permissions;

interface RoleHolder
{
    /**
     * Get user roles 
     * 
     * @return array
     */ 
    public function getRoles();
}