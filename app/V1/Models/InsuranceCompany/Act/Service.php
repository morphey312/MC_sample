<?php

namespace App\V1\Models\InsuranceCompany\Act;

use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\BaseModel;
use App\V1\Models\InsuranceCompany\Act;
use App\V1\Models\Patient;

class Service extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'insurance_act_services';

    /**
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'act_id',
        'service_id',
        'insurance_payment'
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
    
    /**
     * Related insurance act
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurance_act()
    {
        return $this->belongsTo(Act::class, 'act_id');
    }

    /**
     * Related appointment service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment_service()
    {
        return $this->belongsTo(AppointmentService::class, 'service_id');
    }
}
