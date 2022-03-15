<?php

namespace App\V1\Policies\Clinic;

use App\V1\Policies\BasePolicy;

class BonusNormPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'bonus-norms';

    /**
     * @var array
     */
    protected $providedBy = [
        'bonus-norms.access' => [
            'operator-bonuses.access',
        ],
        'bonus-norms.create' => [
            'operator-bonuses.create',
        ],
        'bonus-norms.update' => [
            'operator-bonuses.update',
        ],
        'bonus-norms.delete' => [
            'operator-bonuses.delete',
        ],
    ];
}
