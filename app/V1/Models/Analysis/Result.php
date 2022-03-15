<?php

namespace App\V1\Models\Analysis;

use App\V1\Contracts\Repositories\LaboratoriesScheduleRepository;
use App\V1\Events\Analysis\Result\AnalysisResultSaved;
use App\V1\Models\BaseModel;
use App\V1\Models\Appointment;
use App\V1\Models\Appointment\Service\Item;
use App\V1\Models\CacheValidity;
use App\V1\Models\Patient;
use App\V1\Models\Analysis;
use App\V1\Models\Clinic;
use App\V1\Models\Price;
use App\V1\Models\Employee;
use App\V1\Models\Patient\Card\Assignment;
use Carbon\Carbon;
use App\V1\Mail\Analysis\Result as ResultMail;
use App\V1\Mail\Analysis\SecureResult as SecureResultMail;
use Illuminate\Support\Facades\Log;
use Messenger;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Analysis\Laboratory\Order\Item as LaboratoryItem;
use App\V1\Contracts\Repositories\Analysis\ResultRepository;
use App\V1\Models\Analysis\Laboratory\Container;

class Result extends BaseModel implements ClinicShared, SharedResourceInterface
{
    use HasConstraint, SharedResource;

    const RELATION_TYPE = 'analysis_result';
    const EMAIL_TYPE = 'analysis';

    const STATUS_ASSIGNED = 'assigned';
    const STATUS_ASSIGNED_BUT_NOT_BE_TEST = 'assigned_but_not_be_test';
    const STATUS_PASSED = 'passed';
    const STATUS_TEST_IN_OTHER_LABORATORY = 'test_in_other_laboratory';
    const STATUS_READY = 'ready';
    const STATUS_EMAIL_SENT = 'email_sent';
    const ANALYSIS_RESULTS = 'analysis_results';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analysis_results';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'analysis_id',
        'patient_id',
        'assigner_id',
        'card_specialization_id',
        'card_assignment_id',
        'appointment_id',
        'price_id',
        'clinic_id',
        'custom_name',
        'quantity',
        'cost',
        'discount',
        'date_expected_pass',
        'date_pass',
        'date_expected_ready',
        'date_ready',
        'date_sent_email',
        'attachments',
        'blank_id',
        'blank_data',
        'status',
        'is_outclinic',
        'by_policy',
        'franchise',
        'warranter',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
        'is_outclinic' => 'boolean',
        'by_policy' => 'boolean',
        'blank_data' => 'object',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->initStatus();

            $attempts = 3;
            for ($i = 1; $i <= $attempts; $i++) {
                $code = str_random(8);

                $existingCode = static::where('verification_code', '=', $code)->first();
                if (empty($existingCode)) {
                    $model->verification_code = $code;
                    break;
                }
            }
        });

        static::saved(function ($model) {
            $model->updateCacheValidity();
        });

        static::deleted(function ($model) {
            $model->updateCacheValidity();
        });
    }

    /**
     * Get entity relation type
     *
     * @return string
     */
    public function getRelationType()
    {
        return self::RELATION_TYPE;
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Related analysis
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function analysis()
    {
        return $this->belongsTo(Analysis::class);
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Related price
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * Related attachments
     *
     * @return \App\V1\Repositories\Relations\FileAttachment
     */
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }

    /**
     * Related card assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_assignment()
    {
        return $this->belongsTo(Assignment::class, 'card_assignment_id');
    }

    /**
     * Get related analysis clinic data
     *
     * @return mixed
     */
    public function getAnalysisClinicAttribute()
    {
        return $this->analysis->getClinic($this->clinic_id);
    }

    /**
     * Update status if required
     */
    public function updateStatus()
    {
        if ($this->relationLoaded('attachments')) {
            if ($this->attachments->count() === 0) {
                if ($this->status === self::STATUS_EMAIL_SENT ||
                    $this->status === self::STATUS_READY)
                {
                    $this->status = self::STATUS_PASSED;
                    $this->date_ready = null;
                    $this->date_sent_email = null;
                }
            }
        }

        if ($this->date_sent_email !== null) {
            $this->status = self::STATUS_EMAIL_SENT;
        } elseif ($this->date_ready !== null) {
            $this->status = self::STATUS_READY;
            if ($this->date_expected_ready === null) {
                $this->date_expected_ready = $this->date_ready;
            }
        } elseif ($this->date_pass !== null) {
            $this->status = self::STATUS_PASSED;
            if ($this->date_expected_pass === null) {
                $this->date_expected_pass = $this->date_pass;
            }
            if ($this->date_expected_ready === null) {
                $this->setExpectedReady();
            }
        } elseif ($this->status === null ||
                 ($this->date_pass === null && $this->status === Result::STATUS_PASSED)) {
            $this->status = self::STATUS_ASSIGNED;
        }
    }

    /**
     * Related assigner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner()
    {
        return $this->belongsTo(Employee::class, 'assigner_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related appointment service item
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function appointment_service_item()
    {
        return $this->morphOne(Item::class, 'service');
    }

    /**
     * Send analysis result to email
     *
     * @return bool
     */
    public function sendToPatient()
    {
        $prevStatus = $this->status;
        $pervDateSent = $this->date_sent_email;
        $prevDeliveryStatus = $this->delivery_status;

        $this->delivery_status = Message::STATUS_DELIVERING;

        if ($this->status !== self::STATUS_EMAIL_SENT) {
            $this->status = self::STATUS_EMAIL_SENT;
            $this->date_sent_email = Carbon::now();
        }

        $this->save();

        if ($this->clinic->secure_analysis && Messenger::send(new SecureResultMail($this))) {
            return true;
        } else if(Messenger::send(new ResultMail($this))){
            return true;
        }

        static::where('id', '=', $this->id)->update([
            'status' => $prevStatus,
            'date_sent_email' => $pervDateSent,
            'delivery_status' => $prevDeliveryStatus,
        ]);

        // $this->delivery_status = Message::STATUS_DELIVERY_FAILED;
        // $this->save();
        return false;
    }

    /**
     * Change status on success delivery
     */
    public function deliverySuccess()
    {
        if ($this->delivery_status === Message::STATUS_DELIVERING) {
            static::where('id', '=', $this->id)->update([
                'delivery_status' => Message::STATUS_DELIVERY_OK,
            ]);
        } elseif ($this->delivery_status === Message::STATUS_DELIVERY_FAILED) {
            static::where('id', '=', $this->id)->update([
                'delivery_status' => Message::STATUS_DELIVERY_OK_FAILED,
            ]);
        }
    }

    /**
     * Change status on failed delivery
     */
    public function deliveryFailure()
    {
        if ($this->delivery_status === Message::STATUS_DELIVERING) {
            static::where('id', '=', $this->id)->update([
                'delivery_status' => Message::STATUS_DELIVERY_FAILED,
            ]);
        } elseif ($this->delivery_status === Message::STATUS_DELIVERY_OK) {
            static::where('id', '=', $this->id)->update([
                'delivery_status' => Message::STATUS_DELIVERY_OK_FAILED,
            ]);
        }
    }

    /**
     * Delete analysis result or move it back to assigned
     *
     * @param bool $tryMoveToAssigned
     *
     * @return bool
     */
    public function delete($tryMoveToAssigned = true)
    {
        if ($tryMoveToAssigned &&
            !empty($this->card_assignment_id) &&
            !empty($this->appointment_id) &&
            ($this->status === self::STATUS_ASSIGNED ||
                $this->status === self::STATUS_PASSED)) {
            $repository = app(ResultRepository::class);
            $source = $repository->getAssignedAnalysis($repository->filter([
                'appointment_missing' => 1,
                'card_assignment' => $this->card_assignment_id,
                'analysis' => $this->analysis_id,
                'status' => [self::STATUS_ASSIGNED, self::STATUS_PASSED],
            ]));

            if ($source === null) {
                // just change this record
                if ($this->status === self::STATUS_PASSED) {
                    $this->status = self::STATUS_ASSIGNED;
                    $this->date_pass = null;
                }
                $this->appointment_id = null;
                return $this->save();
            } else {
                // change source record, then this one can be deleted
                $source->quantity += $this->quantity;
                $source->save();
            }
        }

        return parent::delete();
    }

    /**
     * Related registered biomaterial container
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function laboratory_container()
    {
        return $this->belongsToMany(Container::class, 'laboratory_container_results',  'result_id', 'laboratory_container_id');

    }

    /**
     * Clear not yet sent sms reminders for appointment
     */
    public function updateCacheValidity()
    {
        $this->cache_validity()->updateOrCreate(
            [],
            [
                'last_document_action_timestamp' => Carbon::now()->timestamp
            ]
        );
    }

    /**
     * Related treatment course
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cache_validity()
    {
        return $this->hasOne(CacheValidity::class, 'patient_id', 'patient_id');
    }

    /**
     * Set initial status if required
     */
    protected function initStatus()
    {
        if ($this->status === null) {
            if ($this->date_pass !== null) {
                $this->status = self::STATUS_PASSED;
                $this->setExpectedReady();
            } else {
                $this->status = self::STATUS_ASSIGNED;
            }
        }
    }

    /**
     * Set analysis result initial date_expected_ready
     */
    public function setExpectedReady()
    {
        $clinic = $this->analysis_clinic;

        if (!$clinic || empty($clinic->pivot->duration_days)) {
            return;
        }

        $expectedReady = Carbon::parse($this->date_pass)
            ->addDays($clinic->pivot->duration_days);

        $daysOff = $this->getLabOratoriesDaysOff($expectedReady);

        $expectedReady->addDays(count($daysOff));

        $this->date_expected_ready = $expectedReady->toDateString();
    }

    /**
     * @param $expectedReady
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getLabOratoriesDaysOff($expectedReady)
    {
        $scheduleRepository = app(LaboratoriesScheduleRepository::class);
        return $scheduleRepository->all($scheduleRepository->filter([
            'date_start' => $this->date_pass,
            'date_end' => $expectedReady->toDateString(),
        ]));
    }
}
