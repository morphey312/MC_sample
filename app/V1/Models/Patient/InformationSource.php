<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;
use App\V1\Models\Appointment;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Employee;

class InformationSource extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    const RELATION_TYPE = 'information_source';

    /**
     * @var string
     */
    protected $table = 'information_sources';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'is_active',
        'is_collective_form',
        'show_in_appointment',
        'media_type',
        'clinics',
        'employee_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_collective_form' => 'boolean',
        'show_in_appointment' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'patients',
        'appointments',
    ];

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'information_source_clinics', 'source_id', 'clinic_id');
    }

    /**
     * Related patients
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'source_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'source_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
