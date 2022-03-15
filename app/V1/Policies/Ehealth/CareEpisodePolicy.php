<?php

namespace App\V1\Policies\Ehealth;

use App\V1\Models\User;
use App\V1\Policies\BasePolicy;

class CareEpisodePolicy extends BasePolicy
{
    const ACTION_FILL = 'fill';

    /**
     * @var  string
     */
    protected $module = 'ehealth-care-episode';

    /**
     * Check if user can create an entity
     *
     * @param User $user
     *
     * @return bool
     */
    public function fill(User $user)
    {
        return $this->can($user, self::ACTION_FILL);
    }
}
