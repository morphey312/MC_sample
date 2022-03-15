<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Models\DaySheetOwner;
use App\V1\Traits\Models\AppointmentDoctor;
use App\V1\Traits\Models\CallRequestDoctor;

class Workspace extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource,
        HasConstraint,
        DaySheetOwner,
        AppointmentDoctor,
        CallRequestDoctor;

    const RELATION_TYPE = 'workspaces';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'has_day_sheet',
        'is_active',
        'is_operational',
        'is_hospital_room',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'has_day_sheet' => 'boolean',
        'is_active' => 'boolean',
        'is_operational' => 'boolean',
        'is_hospital_room' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'day_sheets',
        'doctor_appointments',
        'workspace_clinics',
    ];

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'workspace_clinics', 'workspace_id', 'clinic_id')
                    ->withPivot('appointment_duration')
                    ->orderBy('name');
    }

    /**
     * Related workspace clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workspace_clinics()
    {
        return $this->hasMany(Workspace\Clinic::class, 'workspace_id');
    }

    /**
     * Get specializations by clinic
     *
     * @param int $clinic
     *
     * @return collection
     */
    public function clinicSpecializations($clinic)
    {
        return object_get($this->getClinicById($clinic), 'specializations', collect([]));
    }

    /**
     * Get workspace clinic by id
     *
     * @param int $clinicId
     *
     * @return Workspace\Clinic
     */
    public function getClinicById($clinicId)
    {
        return $this->workspace_clinics
            ->where('clinic_id', '=', $clinicId)
            ->first();
    }

    /**
     * Get full name attribute
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
