<?php

namespace App\V1\Policies;

use App\V1\Models\User;
use Permissions;

class SessionLogPolicy extends BasePolicy
{
    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return Permissions::has($user, 'process-logs.create');
    }
}
