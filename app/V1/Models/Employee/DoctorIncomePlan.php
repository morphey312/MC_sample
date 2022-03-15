<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Clinic;
use App\V1\Models\Specialization;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class DoctorIncomePlan extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    const RELATION_TYPE = 'doctor_income_plan';

    /**
     * @var string
     */ 
    protected $table = 'doctor_income_plans';

    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'clinic_id',
        'specialization_id',
        'plan_service_mark',
        'year',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
    ];

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }
}
