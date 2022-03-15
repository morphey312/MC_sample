<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;
use Permissions;

class DocumentPolicy extends BasePolicy
{
    /**
     * @inherit
     */
    protected function canCreate($user)
    {
        return Permissions::hasAny($user, ['doctor-cabinet.outpatient-records', 'patient-cabinet.create-documents']) ;
    }

    /**
     * @inherit
     */
    protected function canUpdate($user)
    {
        return Permissions::hasAny($user, ['doctor-cabinet.outpatient-records', 'patient-cabinet.create-documents']) ;
    }

    /**
     * @inherit
     */
    protected function canDelete($user)
    {
        return Permissions::hasAny($user, ['doctor-cabinet.delete-documents', 'patient-cabinet.delete-documents']);
    }
}
