<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee\Doctor;
use Illuminate\Support\Arr;
use App\V1\Models\Employee;
use App\V1\Models\Workspace;
use App\V1\Models\Appointment;
use App\V1\Models\Appointment\Limitation;
use App;
use App\V1\Contracts\Repositories\DaySheetRepository;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class DaySheet extends BaseModel implements ClinicShared
{
    const RELATION_TYPE = 'day_sheet';
    const UNLOCK_DELAY = 3;
    const UNLOCK_DELAY_ENLARGED = 5;
    const REPORT_DOCTOR_DAYSHEET_INDEX_NAME = 'doctor_day_sheet';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'date',
        'day_sheet_owner_id',
        'day_sheet_owner_type',
        'clinic_id',
        'doctor_id',
        'time_sheets',
        'workspace_id',
    ];

    /**
     * @var array
     */
    protected $cascade_delete = [
        'time_sheets',
        'locks',
    ];

    /**
     * @var array
     */
    protected $observables = [
        'timesheetSaved',
        'timesheetDeleting',
    ];

    /**
     * Related owner (employee || workspace)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function day_sheet_owner()
    {
        return $this->morphTo();
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Related owner employee (uses for name filter)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner_employee()
    {
        return $this->belongsTo(Employee::class, 'day_sheet_owner_id')
            ->where('day_sheets.day_sheet_owner_type', Employee::RELATION_TYPE);
    }

    /**
     * Related owner workspace (uses for name filter)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner_workspace()
    {
        return $this->belongsTo(Workspace::class, 'day_sheet_owner_id')
            ->where('day_sheets.day_sheet_owner_type', Workspace::RELATION_TYPE);
    }

    /**
     * Related time sheets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function time_sheets()
    {
        return $this->hasMany(DaySheet\TimeSheet::class, 'day_sheet_id')
            ->orderBy('time_from');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Related time locks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locks()
    {
        return $this->hasMany(DaySheet\Lock::class, 'day_sheet_id');
    }

    /**
     * Related appointments
     *
     * @return \App\V1\Repositories\Relations\ComplexHasMany
     */
    public function appointments()
    {
        return $this->hasManyComplex(Appointment::class, [
            ['date', '=', 'date'],
            ['clinic_id', '=', 'clinic_id'],
            ['workspace_id', '=', 'workspace_id'],
            ['doctor_id', '=', 'day_sheet_owner_id'],
            ['doctor_type', '=', 'day_sheet_owner_type'],
        ], [
            ['is_deleted', '=', '0'],
        ])->orderBy('start');
    }

    /**
     * Related foreign appointments
     *
     * @return \App\V1\Repositories\Relations\ComplexHasMany
     */
    public function foreign_appointments()
    {
        return $this->hasManyComplex(Appointment::class, [
            ['date', '=', 'date'],
            ['clinic_id', '!=', 'clinic_id'],
            ['workspace_id', '=', 'workspace_id'],
            ['doctor_id', '=', 'day_sheet_owner_id'],
            ['doctor_type', '=', 'day_sheet_owner_type'],
        ], [
            ['is_deleted', '=', '0'],
        ])->orderBy('start');
    }

    /**
     * Related limitations
     *
     * @return \App\V1\Repositories\Relations\ComplexHasMany
     */
    public function limitations()
    {
        return $this->belongsToManyComplex(
            Limitation::class,
            'appointment_limitation_doctor',
            'appointment_limitation_id',
            'id',
            [
                ['doctor_id', '=', 'day_sheet_owner_id'],
                ['date_from', '<=', 'date'],
                ['date_to', '>=', 'date'],
                ['clinic_id', '=', 'clinic_id'],
            ])
            ->withPivot('limitation_percent', 'is_hard_limit');
    }

    /**
     * Save related time sheets
     */
    public function saveTimeSheets($timeSheets)
    {
        foreach($timeSheets as $item) {
            $timeSheet = $this->time_sheets()
                              ->create(Arr::only($item, ['time_from', 'time_to']));
            $timeSheet->specializations()
                      ->sync(Arr::get($item, 'specializations'));
        }

        $this->fireModelEvent('timesheetSaved');
    }

    /**
     * @inherit
     */
    public function delete()
    {
        $this->fireModelEvent('timesheetDeleting');

        return parent::delete();
    }

    /**
     * Create related time lock
     *
     * @param array $attributes
     */
    public function createLock($attributes)
    {
        return $this->locks()->create([
            'employee_id' => Arr::get($attributes, 'employee_id'),
            'start' => Arr::get($attributes, 'start'),
            'end' => Arr::get($attributes, 'end'),
            'type' => Arr::get($attributes, 'type', DaySheet\Lock::TYPE_TEMPORARY),
            'comment' => Arr::get($attributes, 'comment', null),
            'reason_id' => Arr::get($attributes, 'reason_id', null),
            'appointment_id' => Arr::get($attributes, 'appointment_id', null),
        ]);
    }

    /**
     * Delete related time lock
     *
     * @param int $id
     */
    public function deleteLock($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        return $this->locks()->whereIn('id', $id)->delete();
    }

    /**
     * Delete related time locks
     *
     * @param array $data
     */
    public function deleteLocks($data)
    {
        $query = $this->locks()->where([
            ['employee_id', '=', Arr::get($data, 'employee_id')],
        ]);

        if (Arr::has($data, 'start')) {
            $query->where('start', '=', $data['start']);
        }

        if (Arr::has($data, 'end')) {
            $query->where('end', '=', $data['end']);
        }
        return $query->delete();
    }

    /**
     * Delete employee locks
     *
     * @param int $id
     */
    public function deleteEmployeeLocks($employee_id)
    {
        return $this->locks()->where('employee_id', '=', $employee_id)->delete();
    }

    /**
     * Get related locks by employee, start, end
     *
     * @param array $data
     *
     * @return collection
     */
    public function getLocks($data)
    {
        return $this->locks()->where([
            ['employee_id', '=', Arr::get($data, 'employee_id')],
            ['start', '=', Arr::get($data, 'start')],
            ['end', '=', Arr::get($data, 'end')],
        ])->get();
    }

    /**
     * Get related doctor daysheets by date and/or workspace
     *
     * @return collection
     */
    public function getRelatedDaySheets()
    {
        $workspaces = $this->getDaySheetWorkspaceId();
        $repository = app(DaySheetRepository::class);
        return $repository->all($repository->filter([
            'date' => $this->date,
            'owner_type' => $this->day_sheet_owner_type,
            'skip_id' => $this->id,
            'or' => [
                [
                    'owner_id' => $this->day_sheet_owner_id
                ],
                [
                    'in_workspaces' => $workspaces
                ],
            ]
        ]));
    }

    /**
     * Get related time sheets specialization workspace_id
     *
     * @return array
     */
    protected function getDaySheetWorkspaceId()
    {
        $time_sheets = $this->time_sheets()
            ->whereHas('specialization_with_workspace')
            ->get();

        return $time_sheets->map(function($item) {
            $id = $item->specializations->pluck('pivot')->pluck('workspace_id');
            return $id;
        })->flatten()->toArray();
    }

    /**
     * Related workspace
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workspace() {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get day sheets by adjacent specialization
     *
     * @param array $skipId
     *
     * @return collection
     */
    public function getAdjacentDaySheets($skipId = []) {
        $specializations = $this->getAdjacentSpecializations();
        $repository = app(DaySheetRepository::class);
        $query = $repository->getFilteredQuery([
            'date' => $this->date,
            'clinic' => $this->clinic_id,
            'specialization' => $specializations,
        ]);

        if (!empty($skipId)) {
            $query->whereNotIn('day_sheets.id', $skipId);
        }

        return $query->get();
    }

    /**
     * Get adjacent specializations for related specializations
     *
     * @return array
     */
    public function getAdjacentSpecializations() {
        $time_sheets = $this->time_sheets()
            ->with(['specializations.adjacent_specializations' => function($query) {
                return $query->where('clinic_id', '=', $this->clinic_id);
            }])->get();

        return $time_sheets->pluck('specializations')
            ->flatten()
            ->pluck('adjacent_specializations')
            ->flatten()
            ->pluck('id')
            ->toArray();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
