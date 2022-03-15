<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;

class PrintedDocumentPolicy extends BasePolicy
{

    /**
     * @inherit
     */
    protected function canCreate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.outpatient-records') ;
    }

    /**
     * @inherit
     */
    protected function canUpdate($user)
    {
        return Permissions::has($user, 'doctor-cabinet.outpatient-records') ;
    }
}
