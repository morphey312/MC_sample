<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Contracts\Repositories\Query\Filter;

abstract class ClinicSharedPolicy extends BasePolicy
{
    const ACTION_ACCESS_CLINIC = 'access-clinic';
    const ACTION_CREATE_CLINIC = 'create-clinic';
    const ACTION_UPDATE_CLINIC = 'update-clinic';
    const ACTION_DELETE_CLINIC = 'delete-clinic';
    
    const CLINIC_FILTER = 'clinic';
    
    /**
     * @inherit
     */
    public function list(User $user, Filter $filter = null)
    {
        if ($this->canAccess($user)) {
            return true;
        }
        
        if ($this->canAccessClinic($user)) {
            return $this->applyClinicFilter($user, $filter);
        }
        
        return false;
    }
    
    /**
     * Check if user has access permissions in scope of clinics
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canAccessClinic($user)
    {
        return $this->can($user, self::ACTION_ACCESS_CLINIC);
    }
    
    /**
     * @inherit
     */
    public function get(User $user, Model $model)
    {
        return ($this->canAccess($user) || 
                    $this->canAccessClinic($user)
                 && $this->accessibleFromClinic($user, $model))
            && $this->isAccessible($model, $user);
    }
    
    /**
     * @inherit
     */
    public function create(User $user)
    {
        return $this->canCreate($user)
            || $this->canCreateClinic($user);
    }
    
    /**
     * Check if user has create permissions in scope of clinics
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canCreateClinic($user)
    {
        return $this->can($user, self::ACTION_CREATE_CLINIC);
    }
    
    /**
     * @inherit
     */
    public function update(User $user, Model $model)
    {
        return ($this->canUpdate($user) ||
                    $this->canUpdateClinic($user)
                 && $this->writableFromClinic($user, $model))
            && $this->isOwnedBy($model, $user);
    }
    
    /**
     * Check if user has update permissions in scope of clinics
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canUpdateClinic($user)
    {
        return $this->can($user, self::ACTION_UPDATE_CLINIC);
    }
    
    /**
     * @inherit
     */
    public function delete(User $user, Model $model)
    {
        return ($this->canDelete($user) ||
                    $this->canDeleteClinic($user)
                 && $this->writableFromClinic($user, $model))
            && $this->isOwnedBy($model, $user);
    }
    
    /**
     * Check if user has delete permissions in scope of clinics
     * 
     * @param User $user
     *
     * @return bool
     */ 
    protected function canDeleteClinic($user)
    {
        return $this->can($user, self::ACTION_DELETE_CLINIC);
    }
    
    /**
     * @inherit
     */
    public function all(User $user, Filter $filter = null)
    {
        return true;
    }
    
    /**
     * Check if user can see entity
     * 
     * @param ClinicShared $user
     * @param ClinicShared $model
     * 
     * @return bool
     */ 
    protected function accessibleFromClinic(ClinicShared $user, ClinicShared $model)
    {
        return 0 !== count(array_intersect(
            $user->getClinicIds(), 
            $model->getClinicIds()
        ));
    }
    
    /**
     * Check if user can manage entity
     * 
     * @param ClinicShared $user
     * @param ClinicShared $model
     * 
     * @return bool
     */ 
    protected function writableFromClinic(ClinicShared $user, ClinicShared $model)
    {
        return count($model->getClinicIds()) === count(array_intersect(
            $user->getClinicIds(), 
            $model->getClinicIds()
        ));
    }
    
    /**
     * Apply clinic filter
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return  bool
     */
    protected function applyClinicFilter(ClinicShared $user, $filter)
    {
        if ($filter !== null) {
            $clinics = (array) $filter->getFilter(self::CLINIC_FILTER);
            $userClinics = $user->getClinicIds();
            
            if (count($clinics) !== 0) {
                $clinics = array_intersect($clinics, $userClinics);
                
                if (count($clinics) === 0) {
                    return false;
                }
                
                $filter->setFilter(self::CLINIC_FILTER, $clinics);
            } else {
                $filter->setFilter(self::CLINIC_FILTER, $userClinics);
            }
        }
        
        return true;
    }
}
