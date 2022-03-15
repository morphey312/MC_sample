<?php

namespace App\V1\Policies\Call;

use App\V1\Policies\ClinicSharedPolicy;
use App\V1\Models\User;
use App\V1\Models\Call\CallLog;
use Permissions;
use App\V1\Contracts\Repositories\Query\Filter;

class CallLogPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'call-logs';
    
    /**
     * @inherit
     */
    public function list(User $user, Filter $filter = null)
    {
        if ($filter->getFilter('missed')) {
            $this->module = 'call-logs-missed';
        }
        
        if ($this->can($user, self::ACTION_ACCESS)) {
            return true;
        }
        
        if ($this->can($user, self::ACTION_ACCESS_CLINIC)) {
            return $this->applyClinicFilter($user, $filter);
        }
        
        return false;
    }
    
    /**
     * Check if user can hold this call
     *
     * @param User $user
     * @param CallLog $model
     *
     * @return bool
     */
    public function active(User $user, CallLog $model)
    {
        return $this->canProcessCalls($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can hold this call
     *
     * @param User $user
     * @param CallLog $model
     *
     * @return bool
     */
    public function hold(User $user, CallLog $model)
    {
        return $this->canProcessCalls($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can create conference on this call
     *
     * @param User $user
     * @param CallLog $model
     *
     * @return bool
     */
    public function bridge(User $user, CallLog $model)
    {
        return $this->canProcessCalls($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can join caller to conference on this call
     *
     * @param User $user
     * @param CallLog $model
     *
     * @return bool
     */
    public function join(User $user, CallLog $model)
    {
        return $this->canProcessCalls($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can remove caller from conference on this call
     *
     * @param User $user
     * @param CallLog $model
     *
     * @return bool
     */
    public function kick(User $user, CallLog $model)
    {
        return $this->canProcessCalls($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can process calls
     *
     * @return bool
     */
    protected function canProcessCalls($user)
    {
        return Permissions::has($user, 'process-logs.create');
    }
}