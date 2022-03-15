<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Traits\Models\EnquiryOperator;
use App\V1\Models\Call\RelatedAction;
use Illuminate\Support\Facades\App;
use App\V1\Contracts\Repositories\WaitListRecordRepository;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Events\Broadcast\WaitListRecordProcessed;
use App\V1\Models\Call\ProcessLog;
use Carbon\Carbon;

class WaitListRecord extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use EnquiryOperator;

    const STATUS_NEW = 'new';
    const STATUS_PROCESSED = 'processed';
    const STATUS_NOT_PROCESSED = 'not_processed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_PAUSE = 'pause';

    /**
     * Model table name
     *
     * @var string
     */
    protected $table = 'wait_list_records';

    /**
     * @var array
     */
    protected $fillable = [
        'period_from',
        'period_to',
        'patient_id',
        'clinic_id',
        'doctor_id',
        'operator_id',
        'specialization_id',
        'cancel_reason',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->status == WaitListRecord::STATUS_NEW)
                $model->assignToOperator();
        });

        static::updating(function ($model) {
            if ($model->getOriginal('status') == WaitListRecord::STATUS_PAUSE && $model->status == WaitListRecord::STATUS_NEW) {
                $model->assignToOperator();

            } elseif ($model->getOriginal('status') == WaitListRecord::STATUS_NEW && $model->status == WaitListRecord::STATUS_PAUSE) {
                $model->operator = null;
            }
        });
    }

    /**
     * Related call
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
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
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Related operator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Get category_group attribute
     *
     * @return string
     */
    public function getCategoryGroupAttribute()
    {
        return EmployeeSiteEnquiryCategory::CATEGORY_DEFAULT;
    }

    /**
     * Related process record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function process()
    {
        return $this->hasOne(Call\ProcessLog::class, 'wait_list_record_id')->orderBy('id', 'asc');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related enquiry service
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function prepayment_service()
    {
        return $this->hasOne(SiteEnquiry\Service::class, 'wait_list_record_id');
    }

     /**
     * Related process log
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function auto_process()
    {
        return $this->belongsTo(ProcessLog::class, 'auto_process_id');
    }

    /**
     * 
     * @param ProcessLog $processLog
     */
    public static function autoprocessRecords($processLog)
    {
        //Get process log created appointments
        $createdAppointments = $processLog->related_actions->filter(function($action) {
            return $action->related_type === RelatedAction::TYPE_APPOINTMENT 
                && $action->action === RelatedAction::ACTION_CREATE;
            })
            ->pluck('related_id')
            ->toArray();

        if (empty($createdAppointments)) {
            return;
        }

        //Get all patient new records
        $recordRepository = App::make(WaitListRecordRepository::class);
        $patientWaitListRecords = $recordRepository->all(
            $recordRepository->filter([
                'patient' => $processLog->contact_id,
                'status' => static::STATUS_NEW,
                'created_end' => Carbon::now()->subDay()->format('Y-m-d'),
            ] + ($processLog->hasWaitListRecord() 
                ? ['skip_id' => $processLog->wait_list_record_id] 
                : [])
            )
        );

        if ($patientWaitListRecords->isEmpty()) {
            return;
        }

        $appointmentRepository = App::make(AppointmentRepository::class);
        $appointments = $appointmentRepository->all(
            $appointmentRepository->filter([
                'id' => $createdAppointments,
                'patient' => $processLog->contact_id,
                'is_deleted' => 0,
            ])
        );

        $patientWaitListRecords->loadMissing('clinic');
        $appointments->loadMissing('clinic');

        //Update all auto processed appointments and broadcast if operator already recieved
        foreach ($patientWaitListRecords as $record) {
            $recordClinicGroup = object_get($record, 'clinic.group_id');

            $recordAppointment = $appointments->first(function($appointment) use ($record, $recordClinicGroup) {
                $appointmentClinicGroup = object_get($appointment, 'clinic.group_id');

                return $appointment->specialization_id === $record->specialization_id
                    && ($appointment->clinic_id === $record->clinic_id || ($appointmentClinicGroup !== null && $appointmentClinicGroup === $recordClinicGroup));
            });

            if ($recordAppointment !== null) {
                $record->status = static::STATUS_PROCESSED;
                $record->auto_process_id = $processLog->id;
                $record->save();

                if ($record->operator_id != null) {
                    broadcast(new WaitListRecordProcessed($record));
                }
            }
        }
    }
}
