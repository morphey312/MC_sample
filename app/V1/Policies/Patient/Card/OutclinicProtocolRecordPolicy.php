<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use App\V1\Models\User;
use Permissions;

class OutclinicProtocolRecordPolicy extends BasePolicy
{
    /**
     * @inheritDoc
     */
    protected function can(User $user, $action)
    {
        return Permissions::hasAny($user,
            ['doctor-cabinet.protocol','doctor-cabinet.add-research', 'patient-cabinet.outclinic-examination-add']);
    }

    protected function canDelete($user)
    {
        return Permissions::hasAny($user,
            ['patient-cabinet.delete-protocols', 'doctor-cabinet.delete-research']);
    }
}
