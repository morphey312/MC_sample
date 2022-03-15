<?php

namespace App\V1\Policies;

use App\V1\Models\User;

class PatientPolicy extends ClinicSharedPolicy
{
    const ACTION_MERGE = 'merge';

    /**
     * @var string
     */
    protected $module = 'patients';

    /**
     * @var array
     */
    protected $providedBy = [
        'patients.access' => [
            'process-logs.create',
            'patient-cabinet.access',
            'payments.create',
            'payments.create-clinic',
            'payments.update',
            'payments.update-clinic',
        ],
    ];

    /**
     * Check if user can merge entities
     *
     * @param User $user
     *
     * @return bool
     */
    public function merge(User $user)
    {
        return $this->can($user, self::ACTION_MERGE);
    }
}
