<?php

namespace App\V1\Policies;

use App\V1\Models\User;

class AnalysisPolicy extends ClinicSharedPolicy
{
    const ACTION_MERGE = 'merge';
    
    /**
     * @var  string
     */ 
    protected $module = 'analyses';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'analyses.access-clinic' => [
            'analysis-prices.access-clinic'
        ],
        'analyses.access' => [
            'analysis-prices.access'
        ],
        'analyses.create' => [
            'analysis-prices.upload'
        ],
        'analyses.update' => [
            'analysis-prices.upload'
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
