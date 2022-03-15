<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\V1\Contracts\Services\Permissions\SharedResource;
use App\V1\Contracts\Repositories\Query\Filter;
use Permissions;

abstract class BasePolicy
{
    use HandlesAuthorization;
    
    const ACTION_ACCESS = 'access';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    
    /**
     * @var string
     */ 
    protected $module;
    
    /**
     * @var array
     */
    protected $providedBy = [];
    
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
        
        if (array_key_exists($permission, $this->providedBy)) {
            foreach ($this->providedBy[$permission] as $perm) {
                if (Permissions::has($user, $perm)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if particular entity is shared to user
     *
     * @param Model $model
     * @param User $user
     * 
     * @return bool
     */
    protected function isAccessible(Model $model, User $user)
    {
        if ($model instanceof SharedResource) {
            return $model->isAccessibleBy($user);
        }
        return true;
    }
    
    /**
     * Check if particular entity is owned by user
     *
     * @param Model $model
     * @param User $user
     *
     * @return bool
     */
    protected function isOwnedBy(Model $model, User $user)
    {
        if ($model instanceof SharedResource) {
            return $model->isOwnedBy($user);
        }
        return true;
    }
    
    /**
     * Check if user can list entities
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return bool
     */
    public function list(User $user, Filter $filter = null)
    {
        return $this->canAccess($user);
    }
    
    /**
     * Check if user has access permissions
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canAccess($user)
    {
        return $this->can($user, self::ACTION_ACCESS);
    }
    
    /**
     * Check if user can access the particular entity
     *
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function get(User $user, Model $model)
    {
        return $this->canAccess($user)
            && $this->isAccessible($model, $user);
    }
    
    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $this->canCreate($user);
    }
    
    /**
     * Check if user has create permissions
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canCreate($user)
    {
        return $this->can($user, self::ACTION_CREATE);
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
        return $this->canUpdate($user)
            && $this->isOwnedBy($model, $user);
    }
    
    /**
     * Check if user has update permissions
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canUpdate($user)
    {
        return $this->can($user, self::ACTION_UPDATE);
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
        return $this->canDelete($user)
            && $this->isOwnedBy($model, $user);
    }
    
    /**
     * Check if user has delete permissions
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canDelete($user)
    {
        return $this->can($user, self::ACTION_DELETE);
    }

    /**
     * Check if user can list all entities
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return  bool
     */
    public function all(User $user, Filter $filter = null)
    {
        return true;
    }
}
