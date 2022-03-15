<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Patient\AssignedMedicine;

class OutclinicMedicine extends BaseModel
{
    const RELATION_TYPE = 'outclinic_medicine';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outclinic_medicines';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'doctor_id',
        'is_apteka24',
        'apteka24_id',
        'apteka24_order_id',
        'is_deleted',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_apteka24' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'assigned_medicines',
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
     * Related assigned medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assigned_medicines()
    {
        return $this->morphMany(AssignedMedicine::class, 'medicine');
    }
}
