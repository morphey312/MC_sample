<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;

class ClientIds extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'value',
        'patient_id',
    ];

    /**
     * @var string
     */
    protected $table = 'patient_client_ids';

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
