<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\BasePolicy;

class CashboxPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'cashboxes';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'cashboxes.access' => [
            'cashier-session-logs.update',
        ],
    ];
}
