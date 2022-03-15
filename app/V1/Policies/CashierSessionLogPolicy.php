<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class CashierSessionLogPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'cashier-session-logs';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'cashier-session-logs.create' => [
            'cashier-session-logs.update',
        ],
    ];
}
