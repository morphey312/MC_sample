<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use \App\V1\Models\Employee\Clinic;

class Doctor extends BaseModel
{
    const WORKSPACE_TYPE = "workspaces";
    const EMPLOYEES_TYPE = "employees";

    /**
     * @var array
     */
    protected $fillable = [
        'appointment_duration',
        'appointment_duration_repeated',
    ];
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee_clinic()
    {
        return $this->belongsTo(Clinic::class, 'employee_clinic_id');
    }
}
