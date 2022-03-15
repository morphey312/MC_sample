<?php

namespace App\V1\Models\DaySheet;

use App\V1\Models\DaySheet\TimeBlockReason;
use Illuminate\Database\Eloquent\Model;
use App\V1\Models\DaySheet;
use App\V1\Models\Employee;
use App\V1\Models\Appointment;

class Lock extends Model
{
    const TYPE_FIXED = 'fixed';
    const TYPE_TEMPORARY = 'temporary';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'day_sheet_locks';

    /**
     * @var array
     */
    protected $fillable = [
        'start',
        'end',
        'day_sheet_id',
        'comment',
        'type',
        'reason_id',
        'employee_id',
        'appointment_id',
    ];

    /**
     * Related day sheet
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function day_sheet()
    {
        return $this->belongsTo(DaySheet::class, 'day_sheet_id');
    }

    /**
     * Related day sheet
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(TimeBlockReason::class, 'reason_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
}
