<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use \App\V1\Models\Employee;
use \App\V1\Models\PaymentMethod;
use App\V1\Contracts\Repositories\Employee\CashboxRepository;

class Cashbox extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'cashier_id',
        'clinic_id',
        'payment_method_id',
        'initial_amount',
        'income',
        'expense',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Related cashier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    /**
     * Related payment_method
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Get cashbox name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->payment_method->i18n('name');
    }
}
