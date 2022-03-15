<?php

namespace App\V1\Traits\Controllers;

use App\V1\Events\Broadcast\DaySheetLock;
use App\V1\Http\Resources\DaySheet\LockResource;
use App\V1\Models\DaySheet;
use App\V1\Models\DaySheet\Lock as LockModel;
use App\V1\Models\DaySheet\TimeBlockReason;
use App\V1\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\V1\Jobs\UnlockDaySheetTime;
use App\V1\Contracts\Repositories\DaySheet\LockRepository;
use App\V1\Contracts\Repositories\DaySheet\TimeBlockReasonRepository;

trait DaySheetLocker
{
    /**
     * Broadcast lock and unlock to queue
     *
     * @param DaySheet $model
     */
    protected function broadcastLock($model)
    {
        $model->locks->loadMissing([
            'appointment' => function($query) {
                $query->with(['patient', 'appointment_services.service']);
            },
        ]);

        broadcast(new DaySheetLock([
            $model->id => new LockResource($model->locks)
        ]));
    }

    /**
     * Add related new lock to queue for delayed unlock
     *
     * @param array $lockData
     * @param $model
     */
    protected function addLockToQueue($model, $lockData, $delay = DaySheet::UNLOCK_DELAY)
    {
        if (empty($lockData) || (isset($lockData['type']) && $lockData['type'] === LockModel::TYPE_FIXED)) {
            return;
        }

        UnlockDaySheetTime::dispatch($lockData, $model->id)
            ->delay(now()->addMinutes($delay));
    }

    /**
     * Lock doctor daysheets by start, end, employee
     *
     * @param DaySheet $model
     * @param array $lockData
     * @param int $delay
     */
    protected function lockSameDoctor($model, $lockData, $delay = DaySheet::UNLOCK_DELAY)
    {
        $doctorDaySheets = $model->getRelatedDaySheets();

        if ($doctorDaySheets->isEmpty()) {
            return;
        }

        $employee = Auth::user()->getEmployeeModel();

        $doctorDaySheets->each(function($item) use ($lockData, $employee, $delay) {
            if (empty($lockData)) {
                $item->deleteEmployeeLocks($employee->id);
            } else {
                $item->createLock($lockData);
            }

            $this->broadcastLock($item);
            $this->addLockToQueue($item, $lockData, $delay);
        });
    }

    /**
     * Unlock doctor daysheets by start, end, employee
     *
     * @param DaySheet $model
     * @param array $lockData
     */
    protected function unlockSameDoctor($model, $lockData)
    {
        $doctorDaySheets = $model->getRelatedDaySheets();

        if ($doctorDaySheets->isEmpty()) {
            return;
        }

        $doctorDaySheets->each(function($item) use ($lockData) {
            $item->deleteLocks($lockData);
            $this->broadcastLock($item);
        });
    }

    /**
     * Unlock doctor daysheets by employee
     *
     *  @param DaySheet $model
     */
    protected function unlockRelatedSheets($model)
    {
        $doctorDaySheets = $model->getRelatedDaySheets();

        if ($doctorDaySheets->isEmpty()) {
            return;
        }

        $employeeId = Auth::user()->getEmployeeId();

        $doctorDaySheets->each(function($item) use ($employeeId) {
            $item->deleteLocks(['employee_id' => $employeeId]);
            $this->broadcastLock($item);
        });
    }

    /**
     * Lock or unlock surgery doctors day sheets
     *
     * @param mixed $prevDoctors
     * @param array $currentEmployees
     * @param Appointment $appointment
     */
    protected function blockSurgeryEmployees($prevDoctors, $currentEmployees, $appointment)
    {
        $employeeIds = array_map(function($item) {
            return $item['employee_id'];
        }, $currentEmployees);

        $missingDoctors = $prevDoctors->filter(function($employee) use ($employeeIds) {
            return !in_array($employee->id, $employeeIds);
        });
        $appointmentLocks = $this->getSurgeryLocks($appointment->id);
        $blockReasonId = $this->getSurgeryReasonId();

        if ($missingDoctors->isNotEmpty()) {
            foreach($missingDoctors as $employee) {
                $doctorLock = $this->getDoctorLock($appointmentLocks, $employee->id);
                if ($doctorLock) {
                    $daySheet = $doctorLock->day_sheet;
                    $doctorLock->delete();
                    $this->broadcastLock($daySheet);
                    $this->unlockSameDoctor($daySheet, $this->getLockData($appointment, $blockReasonId));
                }
            }
        }

        if ($appointment->surgery_employees->isNotEmpty()) {
            foreach($appointment->surgery_employees as $employee) {
                $doctorLock = $this->getDoctorLock($appointmentLocks, $employee->id);
                $lockData = $this->getLockData($appointment, $blockReasonId);
                $daySheet = $employee->getDaySheetByClinicDate($appointment->date, $appointment->clinic_id);
                if ($daySheet) {
                    if ($doctorLock) {
                        if (($doctorLock->start === $appointment->start) && ($doctorLock->end === $appointment->end)) {
                            continue;
                        }
                        $doctorLock->delete();
                        $this->unlockSameDoctor($daySheet, $lockData);
                    }
                    $daySheet->locks()->create($lockData);
                    $this->broadcastLock($daySheet);
                    $this->lockSameDoctor($daySheet, $lockData);
                }
            }
        }
    }

    /**
     * Get lock model data
     *
     * @param Appointment $appointment
     * @param int|null $blockReasonId
     *
     * @return array
     */
    protected function getLockData($appointment, $blockReasonId)
    {
        return [
            'start' => $appointment->start,
            'end' => $appointment->end,
            'employee_id' => Auth::user()->getEmployeeId(),
            'type' => LockModel::TYPE_FIXED,
            'appointment_id' => $appointment->id,
            'reason_id' => $blockReasonId,
        ];
    }

    /**
     * Get lock by employee_id
     *
     * @param mixed $locks
     * @param int $doctorId
     *
     * @return mixed
     */
    protected function getDoctorLock($locks, $doctorId) {
        return $locks->first(function($lock) use ($doctorId) {
            return $lock->day_sheet->day_sheet_owner_id === $doctorId
                && $lock->day_sheet->day_sheet_owner_type === Employee::RELATION_TYPE;
        });
    }

    /**
     * Get doctor day sheet lock by appointment
     *
     * @param int $appointmentId
     *
     * @return mixed
     */
    protected function getSurgeryLocks($appointmentId)
    {
        $repository = app(LockRepository::class);
        return $repository->getFilteredQuery([
            'appointment_id' => $appointmentId,
        ])
        ->with('day_sheet')->get();
    }

    /**
     * Get surgery time block reason
     *
     * @return mixed
     */
    protected function getSurgeryReasonId()
    {
        $repository = app(TimeBlockReasonRepository::class);
        $reason = $repository->getFilteredQuery([
            'is_operation' => 1,
        ])->first();

        return $reason ? $reason->id : null;
    }
}
