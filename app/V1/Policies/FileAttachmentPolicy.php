<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;
use App\V1\Models\User;

class FileAttachmentPolicy extends BasePolicy
{
    const ACTION_REGISTER = 'register';

    /**
     * @var string
     */ 
    protected $module = 'files';

    /**
     * @inherit
     */ 
    protected function can(User $user, $action)
    {
        return true;
    }

    /**
     * Check if user can register an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function register(User $user)
    {
        return parent::can($user, self::ACTION_REGISTER);
    }
}
