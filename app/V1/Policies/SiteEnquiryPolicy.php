<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;
use App\V1\Models\User;
use App\V1\Contracts\Repositories\Query\Filter;

class SiteEnquiryPolicy extends ClinicSharedPolicy
{
    const ACTION_PROCESS = 'process';
    const OPERATOR_FILTER = 'operator';
    
    /**
     * @var  string
     */ 
    protected $module = 'site-enquiries';
    
    /**
     * @inherit
     */
    public function list(User $user, Filter $filter = null)
    {
        if ($this->canAccess($user)) {
            return true;
        }
        
        if ($this->canAccessClinic($user)) {
            if ($this->applyClinicFilter($user, $filter)) {
                return true;
            }
        }
        
        if ($this->can($user, self::ACTION_PROCESS)) {
            return $this->applyOperatorFilter($user, $filter);
        }
        
        return false;
    }
    
    /**
     * Apply operator filter
     *
     * @param User $user
     * @param Filter $filter
     *
     * @return  bool
     */
    protected function applyOperatorFilter($user, $filter)
    {
        if (! $user->isEmployee()) {
            return false;
        }
        
        if ($filter !== null) {
            $filter->setFilter(self::OPERATOR_FILTER, $user->getEmployeeId());
        }
        
        return true;
    }
}
