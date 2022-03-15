<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class MoneyRecieverCashboxPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'money-reciever-cashboxes';
}
