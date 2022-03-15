<?php

namespace App\V1\Models\Payment;

use App\V1\Models\BaseModel;
use App\V1\Models\Payment;

class Check extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_checks';

    /**
     * Related cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'clinic_id');
    }
}