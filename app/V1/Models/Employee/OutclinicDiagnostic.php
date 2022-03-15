<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;

class OutclinicDiagnostic extends BaseModel
{
    const RELATION_TYPE = 'outclinic_diagnostic';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outclinic_diagnostics';

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
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class);
    }
}
