<?php

namespace App\V1\Models\Analysis;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Analysis;
use App\V1\Models\FileAttachment;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Template extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    /**
     * @var string
     */
    protected $table = 'analysis_templates';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'file_id',
        'stamp_file_id',
        'clinics',
        'analyses',
        'laboratories',
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
     * Stamp file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stamp_file()
    {
        return $this->belongsTo(FileAttachment::class, 'stamp_file_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'analysis_template_clinics', 'template_id', 'clinic_id');
    }

    /**
     * Related laboratories
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function laboratories()
    {
        return $this->belongsToMany(Laboratory::class, 'analysis_template_laboratories', 'template_id', 'laboratory_id');
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function analyses()
    {
        return $this->belongsToMany(Analysis::class, 'analysis_template_analyses', 'template_id', 'analysis_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
