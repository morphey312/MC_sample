<?php

namespace App\V1\Policies\Analysis\Laboratory\Order;

use App\V1\Policies\BasePolicy;

class ItemPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'laboratory-order-items';

    /**
     * @var array
     */
    protected $providedBy = [
        'laboratory-order-items.create' => [
            'laboratory-orders.create',
            'laboratory-orders.create-clinic'
        ],
        'laboratory-order-items.access' => [
            'laboratory-orders.access',
            'laboratory-orders.access-clinic'
        ],
        'laboratory-order-items.delete' => [
            'laboratory-orders.delete',
            'laboratory-orders.delete-clinic'
        ],
    ];
}
