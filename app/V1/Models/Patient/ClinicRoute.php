<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Specialization;
use App\V1\Models\FileAttachment;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class ClinicRoute extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    /**
     * @var string
     */
    protected $table = 'patient_clinic_routes';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'file_id',
        'specializations',
    ];

    /**
     * Related file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(FileAttachment::class, 'file_id');
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'patient_clinic_route_specialization', 'clinic_route_id', 'specialization_id');
    }
}
