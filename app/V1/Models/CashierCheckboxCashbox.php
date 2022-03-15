<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class CashierCheckboxCashbox extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'money_reciever_cashbox_id',
        'login',
        'password',
    ];

    /**
     * Related money reciever cashbox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function money_reciever_cashbox()
    {
        return $this->belongsTo(MoneyRecieverCashbox::class, 'money_reciever_cashbox_id');
    }
}
