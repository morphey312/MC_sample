<?php

namespace App\V1\Models\Analysis\Laboratory;

use App\V1\Models\Analysis\Result;
use App\V1\Models\BaseModel;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Patient;
use App\V1\Traits\Permissions\SharedResource;


class Container extends BaseModel implements ClinicShared, SharedResourceInterface
{
    use HasConstraint, SharedResource;

    protected $table = 'laboratory_containers';

      /**
     * @var array
    */
    protected $fillable = [
        'container_id',
        'biomaterial_id',
        'patient_id',
        'barcode',
        'results',
    ];

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function results()
    {
        return $this->belongsToMany(Result::class, 'laboratory_container_results', 'laboratory_container_id', 'result_id');
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
