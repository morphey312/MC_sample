<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic\MoneyReciever;

class MoneyRecieverCashbox extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'money_reciever_id',
        'name',
        'cashbox_key'
    ];

    /**
     * Related money_reciever
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function money_reciever()
    {
        return $this->belongsTo(MoneyReciever::class, 'money_reciever_id');
    }

    /**
     * Related money_reciever
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function cashier_checkbox_cashboxes()
    {
        return $this->hasMany(CashierCheckboxCashbox::class);
    }
}
