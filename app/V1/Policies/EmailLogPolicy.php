<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;
use Permissions;

class EmailLogPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canAccess($user)
    {
        return Permissions::has($user, 'action-logs.email');
    }
}
