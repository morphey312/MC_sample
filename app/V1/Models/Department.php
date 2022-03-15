<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Department extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'clinic_id',
    ];

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
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
     * Related rooms
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms()
    {
        return $this->hasMany(Department\Room::class, 'department_id');
    }
}
