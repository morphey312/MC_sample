<?php

namespace App\V1\Policies;

use App\V1\Models\User;

class ServicePolicy extends ClinicSharedPolicy
{
    const ACTION_MERGE = 'merge';

    /**
     * @var  string
     */
    protected $module = 'services';

    /**
     * @var array
     */
    protected $providedBy = [
        'services.access-clinic' => [
            'doctor-cabinet.access',
            'service-prices.access-clinic'
        ],
        'services.access' => [
            'service-prices.access'
        ],
        'services.create' => [
            'service-prices.upload'
        ],
        'services.update' => [
            'service-prices.upload',
            'services.update-pc-only'
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
