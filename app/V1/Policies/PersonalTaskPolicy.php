<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;
use App\V1\Contracts\Repositories\Query\Filter;

class PersonalTaskPolicy extends ClinicSharedPolicy
{
    const OPERATOR_FILTER = 'operator';
    
    /**
     * @var string
     */
    protected $module = 'personal-tasks';
    
    /**
     * @inherit
     */
    public function list(User $user, Filter $filter = null)
    {
        if ($this->can($user, self::ACTION_ACCESS)) {
            return true;
        }
        
        if ($this->can($user, self::ACTION_ACCESS_CLINIC)) {
            return $this->applyClinicFilter($user, $filter);
        }
        
        if ($user->isEmployee()) {
            $filter->setFilter(self::OPERATOR_FILTER, $user->getEmployeeId());
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can update status on particular entity
     *
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function status(User $user, Model $model)
    {
        return $model->operator_id === $user->getEmployeeId();
    }
}
