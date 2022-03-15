<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic;
use App\V1\Models\Service;
use App\V1\Models\FileAttachment;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class ProtocolTemplate extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    /**
     * @var string
     */
    protected $table = 'protocol_templates';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'file_id',
        'specialization_id',
        'clinics',
        'services',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'records',
    ];

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

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
     * Related records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records()
    {
        return $this->hasMany(ProtocolRecord::class, 'template_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'protocol_template_clinics', 'template_id', 'clinic_id');
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'protocol_template_services', 'template_id', 'service_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
