<?php

namespace App\V1\Models\Checkbox;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Employee\Cashbox;
use App\V1\Models\MoneyRecieverCashbox;

class Check extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'checkbox_checks';


    const TYPE_TOKEN = 'token';
    const TYPE_EXTRACT = 'extract';
    const TYPE_X_REPORT = 'xreport';
    const TYPE_z_REPORT = 'zreport';

    /**
     * @var array
     */
    protected $fillable = [
        'body',
        'cashbox_id',
        'amount',
        'employee_id',
        'money_reciever_cashbox_id',
        'type',
    ];


    /**
     * Related cashbox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class, 'cashbox_id');
    }

     /**
      * Related employee
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
