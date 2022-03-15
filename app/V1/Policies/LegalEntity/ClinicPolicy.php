<?php

namespace App\V1\Policies\LegalEntity;

use App\V1\Policies\BasePolicy;
use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;

class ClinicPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'legal-entities';

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
            || $this->can($user, self::ACTION_UPDATE);
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
        return ($this->can($user, self::ACTION_CREATE) || $this->can($user, self::ACTION_UPDATE))
            && $this->isOwnedBy($model->legal_entity, $user);
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
