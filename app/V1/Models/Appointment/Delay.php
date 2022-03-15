<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ResourceHolder;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use Illuminate\Support\Facades\Auth;

class Delay extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_delays';

    /**
     * @var array
     */
    protected $fillable = [
        'delay_reason_id',
        'appointment_id',
        'comment',
        'duration',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user instanceof ResourceHolder) {
                $model->delayed_by_id = $user->getEmployeeId();
            }
        });
    }

    /**
     * Related delay reason
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delay_reason()
    {
        return $this->belongsTo(Status\DelayReason::class, 'delay_reason_id');
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'delayed_by_id');
    }
}
