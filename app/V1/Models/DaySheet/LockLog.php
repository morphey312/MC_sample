<?php

namespace App\V1\Models\DaySheet;

use Auth;
use App\V1\Models\DaySheet;
use App\V1\Models\Employee;
use App\V1\Models\BaseModel;
use App\V1\Models\Appointment;
use App\V1\Models\ReasonUnblock;
use App\V1\Models\Specialization;
use App\V1\Models\Employee\Clinic;
use Illuminate\Database\Eloquent\Model;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\DaySheet\TimeBlockReason;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee\Clinic as EmployeeClinic;

class LockLog extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    const TYPE_FIXED = 'fixed';
    const TYPE_TEMPORARY = 'temporary';
    const RELATION_TYPE = 'lock_log';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'day_sheet_locks_logs';

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
        'reason_off',
        'employee_id',
        'appointment_id',
        'status'
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
     * Related reason
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(TimeBlockReason::class, 'reason_id');
    }

    /**
     * Related reason_off
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason_off()
    {
        return $this->belongsTo(ReasonUnblock::class, 'reason_off_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->day_sheet->clinic_id];
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
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee_off()
    {
        return $this->belongsTo(Employee::class, 'employee_off_id');
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
     * Get doctor day sheet lock by appointment
     *
     * @param int $appointmentId
     *
     * @return mixed
     */
    public static function getSurgeryLocks($appointmentId)
    {
        return static::where('appointment_id', '=', $appointmentId)->with('day_sheet')->get();
    }
}
