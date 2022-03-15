<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Patient\AssignedSpecialization;

class OutclinicSpecialization extends BaseModel
{
    const RELATION_TYPE = 'outclinic_specialization';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outclinic_specializations';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'doctor_id',
        'is_deleted',
        'is_outclinic'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_outclinic' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'assigned_specializations',
    ];

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Related assigned specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assigned_specializations()
    {
        return $this->morphMany(AssignedSpecialization::class, 'specialization');
    }
}
