<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;

class Account extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'patient_accounts';

    /**
     * @var boolean
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'balance',
        'clinic_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'balance' => 'float',
    ];

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}