<?php

namespace App\V1\Models\Ehealth\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Ehealth\Patient;

class Authentication extends BaseModel
{
    const ACTION_INSERT = 'INSERT';
    const ACTION_DEACTIVATE = 'DEACTIVATE';
    const ACTION_UPDATE = 'UPDATE';

    const OTP_TYPE = 'otp';

    const RELATION_TYPE = 'ehealth_patient_authentication';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'ehealth_patient_authentications';

    /**
     * @var array
     */
    protected $fillable = [
        'ehealth_patient_id',
        'type',
        'value',
        'alias',
        'phone_number',
        'ehealth_id',
        'ended_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'urgent' => 'object',
    ];

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'ehealth_patient_id');
    }
}
