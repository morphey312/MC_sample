<?php

namespace App\V1\Models\Workspace;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Models\Workspace;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic as GenericClinic;

class Clinic extends BaseModel implements ClinicShared
{
        const RELATION_TYPE = 'workspace_clinic';

    /**
     * @var array
     */
    protected $fillable = [
        'workspace_id',
        'clinic_id',
        'appointment_duration',
        'sip_number',
        'specializations',
    ];

    /**
     * @var string
     */
    protected $table = 'workspace_clinics';

    /**
     * @var array
     */
    public $specializationsToSave = null;

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->specializationsToSave !== null) {
                $model->saveSpecializations($model->specializationsToSave);
            }
        });
    }

    /**
     * Set specializations to save
     *
     * @param mixed $value
     */
    public function setSpecializationsAttribute($value)
    {
        $this->specializationsToSave = $value;
    }

    /**
     * Related workspace
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(GenericClinic::class, 'clinic_id');
    }

    /**
     * Related specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'workspace_specialization', 'workspace_clinic_id', 'specialization_id');
    }

    /**
     * Save worksapce specializations
     *
     * @param array $list
     */
    public function saveSpecializations(array $list)
    {
        $this->specializations()->sync($list);
    }

    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
