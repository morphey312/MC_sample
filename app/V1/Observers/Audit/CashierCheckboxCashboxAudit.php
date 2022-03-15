<?php

namespace App\V1\Observers\Audit;

class CashierCheckboxCashboxAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'employee_id',
        'money_reciever_cashbox_id',
        'login',
        'password',
    ];
}
