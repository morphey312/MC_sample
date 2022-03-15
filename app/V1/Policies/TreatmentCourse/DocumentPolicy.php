<?php

namespace App\V1\Policies\TreatmentCourse;

use App\V1\Policies\BasePolicy;
use Permissions;
use App\V1\Models\User;

class DocumentPolicy extends BasePolicy
{
    /**
     * @inherit
     */ 
    protected function canCreate($user)
    {
        return Permissions::has($user, 'patient-cabinet.treatment-course-edit');
    }

    /**
     * @inherit
     */ 
    protected function canUpdate($user)
    {
        return Permissions::has($user, 'patient-cabinet.treatment-course-edit');
    }

    /**
     * Check if user can sign documents
     * 
     * @param User $user
     * 
     * @return bool
     */ 
    public function sign(User $user)
    {
        return Permissions::has($user, 'patient-cabinet.treatment-course-edit');
    }
}
