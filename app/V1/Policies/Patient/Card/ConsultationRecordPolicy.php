<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use App\V1\Models\User;
use Permissions;

class ConsultationRecordPolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function can(User $user, $action)
    {
        return Permissions::has($user, 'doctor-cabinet.issue-referral');
    }
}
