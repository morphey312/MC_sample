<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Clinic\ServiceType as ClinicServiceType;

class ServiceType extends BaseModel
{
    const RELATION_TYPE = 'employee_service_type';

    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'service_id',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'employee_service_types';

    /**
     * @var array
     */
    protected $dates = [
        'sent_to_ehealth_at',
        'start_date',
        'end_date',
    ];

    /**
     * @var array
     */ 
    protected $casts = [
        'is_deleted' => 'boolean',
    ];

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
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_type()
    {
        return $this->belongsTo(ClinicServiceType::class, 'service_id');
    }
}
