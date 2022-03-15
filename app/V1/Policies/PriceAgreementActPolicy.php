<?php

namespace App\V1\Policies;

use App\V1\Models\User;
use App\V1\Policies\BasePolicy;
use Illuminate\Database\Eloquent\Model;

class PriceAgreementActPolicy extends BasePolicy
{
    const ACTION_APPROVE_ACT  = 'approve';

    /**
     * @var  string
     */
    protected $module = 'price-agreement-acts';

    /**
     * @var array
     */
    protected $providedBy = [
        'price-agreement-acts.create' => [
            'price-agreement-acts.create-analysis-clinic',
            'price-agreement-acts.create-services-clinic',
            'price-agreement-acts.create-analysis',
            'price-agreement-acts.create-services',
        ],
        'price-agreement-acts.access' => [
            'price-agreement-acts.access',
            'price-agreement-acts.access-clinic',
        ],
        'price-agreement-acts.approve' => [
            'price-agreement-acts.approve',
        ],
    ];

    /**
     * Check if user has delete permissions in scope of clinics
     *
     * @param User $user
     *
     * @return bool
     */
    protected function canApprove($user)
    {
        return $this->can($user, self::ACTION_APPROVE_ACT);
    }

    /**
     * @inherit
     */
    public function approve(User $user)
    {
        return $this->canApprove($user);
    }
}
