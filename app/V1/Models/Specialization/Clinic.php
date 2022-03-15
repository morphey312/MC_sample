<?php

namespace App\V1\Models\Specialization;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Models\Clinic as GenericClinic;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic\MoneyReciever;

class Clinic extends BaseModel implements ClinicShared
{
    /**
     * @var string
     */
    protected $table = 'clinic_specialization';

    /**
     * @var array
     */
    protected $fillable = [
        'specialization_id',
        'clinic_id',
        'first_patient_appointment_limit',
        'status',
        'days_since_last_visit',
        'show_days_since_message',
        'money_reciever_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'show_days_since_message' => 'boolean',
    ];

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(GenericClinic::class, 'clinic_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related money reciever
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function money_reciever()
    {
        return $this->belongsTo(MoneyReciever::class, 'money_reciever_id');
    }

    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
