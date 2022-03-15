<?php

namespace App\V1\Models\Checkbox;

use App\V1\Models\BaseModel;
use App\V1\Models\CashierCheckboxCashbox;
use App\V1\Models\Employee;
use App\V1\Models\MoneyRecieverCashbox;

class Shift extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'checkbox_shifts';

    /**
     * @var array
     */
    protected $fillable = [
        'money_reciever_cashbox_id',
        'access_token',
        'employee_id',
    ];

    /**
     * Related cashier checkbox cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cashier_checkbox_cashboxes()
    {
        return $this->hasMany(CashierCheckboxCashbox::class, 'money_reciever_cashbox_id', 'money_reciever_cashbox_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'delayed_by_id');
    }

    /**
     * Related money reciever cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function money_reciever_cashbox()
    {
        return $this->belongsTo(MoneyRecieverCashbox::class, 'money_reciever_cashbox_id');
    }
}
