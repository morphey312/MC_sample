<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Appointment;
use App\V1\Traits\Models\HasConstraint;

class DeleteReason extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_delete_reasons';
    
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'include_to_report',
        'not_use_for_appointment_delete',
        'comment_required',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'include_to_report' => 'boolean',
        'not_use_for_appointment_delete' => 'boolean',
        'comment_required' => 'boolean',
    ];

     /**
     * @var array
     */
    protected $deleting_constraints = [
        'appointments',
    ];

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'appointment_delete_reason_id');
    }
}
