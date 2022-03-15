<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class ArchivedNumberPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canAccess($user)
    {
        return Permissions::has($user, 'patient-cabinet.outpatient-cards') ;
    }

    /**
     * @inherit
     */
    public function update($user, $model)
    {
        return Permissions::has($user, 'patient-cabinet.create-archive-card') ;
    }
}
