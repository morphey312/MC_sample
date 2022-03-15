<?php

namespace App\V1\Policies;

use Illuminate\Database\Eloquent\Model;
use App\V1\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'employees';
    
    /**
     * Check if user can see password
     *
     * @param User $user
     * @param Model $model
     *
     * @return bool
     */
    public function viewPassword(User $user, Model $model)
    {
        return $this->can($user, 'view-password');
    }
}
