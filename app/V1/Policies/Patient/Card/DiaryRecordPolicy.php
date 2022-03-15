<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class DiaryRecordPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canCreate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.outpatient-records');
    }
    /**
     * @inherit
     */
    protected function canUpdate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.outpatient-records');
    }

    /**
     * @inherit
     */
    protected function canDelete($user)
    {
        return Permissions::has($user, 'doctor-cabinet.outpatient-records');
    }
}
