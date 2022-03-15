<?php

namespace App\V1\Models\Employee;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee\Clinic;
use App\V1\Traits\Models\HasConstraint;

class Position extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'ehealth_position',
        'ehealth_type',
        'has_specialization',
        'is_doctor',
        'is_operator',
        'is_cashier',
        'has_voip',
        'is_marketing',
        'is_reception',
        'is_collector',
        'is_superviser',
        'is_surgery',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'has_specialization' => 'boolean',
        'is_doctor' => 'boolean',
        'is_operator' => 'boolean',
        'is_cashier' => 'boolean',
        'has_voip' => 'boolean',
        'is_marketing' => 'boolean',
        'is_reception' => 'boolean',
        'is_collector' => 'boolean',
        'is_superviser' => 'boolean',
        'is_surgery' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'employee_clinics',
    ];

    /**
     * Related employee clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_clinics()
    {
        return $this->hasMany(Clinic::class, 'position_id');
    }

    public function getClinicIds()
    {
        return $this->employee_clinics->pluck('id')->all();
    }
}
