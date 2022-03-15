<?php

namespace App\V1\Policies\Department;

use App\V1\Policies\BasePolicy;
use App\V1\Policies\ClinicSharedPolicy;
use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;

class RoomPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'departments';

    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $this->can($user, self::ACTION_CREATE) 
            || $this->can($user, self::ACTION_UPDATE)
            || $this->can($user, self::ACTION_CREATE_CLINIC) 
            || $this->can($user, self::ACTION_UPDATE_CLINIC);
    }
    
    /**
     * Check if user can update the particular entity
     *
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function update(User $user, Model $model)
    {
        return $this->can($user, self::ACTION_CREATE) 
            || $this->can($user, self::ACTION_UPDATE)
            || $this->can($user, self::ACTION_CREATE_CLINIC) 
            || $this->can($user, self::ACTION_UPDATE_CLINIC);
    }
    
    /**
     * Check if user can delete the particular entity
     *
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function delete(User $user, Model $model)
    {
        return $this->update($user, $model);
    }
}
