<?php

namespace App\V1\Models\Handbook;

use App\V1\Models\BaseModel;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic;
use App\V1\Models\FileAttachment;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class PatientDocument extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    /**
     * @var string
     */
    protected $table = 'patient_documents';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_ua',
        'clinic_id',
        'file_id',
        'specializations',
        'is_official_form',
        'is_conclusion',
    ];
    
    /**
     * @var array
     */
    protected $casts = [
        'is_official_form' => 'boolean',
        'is_conclusion' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $cascade_delete = [
        'specializations',
        'file'
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
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'patient_documents_specializations', 'patient_document_id', 'specialization_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
