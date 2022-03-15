<?php

namespace App\V1\Models\Appointment\Status;

use App\V1\Models\BaseModel;
use App\V1\Models\Appointment\Status;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class DelayReason extends BaseModel implements SharedResourceInterface
{
    use HasConstraint, SharedResource;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "status_delay_reasons";

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'statuses',
    ];

    /**
     * Related appointment status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function statuses()
    {
        return $this->belongsToMany(Status::class, 'appointment_status_delay_reason', 'delay_reason_id', 'appointment_status_id');
    }
}
