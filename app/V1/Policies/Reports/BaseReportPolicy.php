<?php

namespace App\V1\Policies\Reports;

use App\V1\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Permissions;
use Illuminate\Support\Str;

abstract class BaseReportPolicy
{
    use HandlesAuthorization;
    
    /**
     * @var string
     */ 
    protected $module;
    
    /**
     * Check if user can perform action on the module
     * 
     * @param User $user
     * @param string $action
     *
     * @return bool
     */ 
    protected function can(User $user, $action)
    {
        $permission = $this->module . '.' . $action;
        
        if (Permissions::has($user, $permission)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * General behaviour
     * 
     * @param string $action
     * @param array $arguments
     * 
     * @return bool
     */ 
    public function __call($action, $arguments) 
    {
        return $this->can($arguments[0], Str::snake($action, '-'));
    }
}
