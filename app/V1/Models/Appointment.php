<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Events\Appointment\AppointmentCreated;
use App\V1\Events\Appointment\AppointmentUpdated;
use App\V1\Jobs\SendOneSTransactions;
use App\V1\Models\Appointment\Note;
use App\V1\Models\Patient\AppointmentDocument;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Models\Appointment\DeleteReason;
use Illuminate\Support\Facades\Auth;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\Appointment\Service\Item as AppointmentServiceItem;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Patient\Card\TreatmentAssignment;
use App\V1\Models\Patient\Card\OutpatientRecord;
use App\V1\Traits\Models\HasCachedAttributes;
use App\V1\Models\Appointment\Status\Reason;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Contracts\Repositories\Appointment\StatusRepository;
use App;

class Appointment extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use HasConstraint;
    use HasCachedAttributes;

    const RELATION_TYPE = 'appointment';

    const CALL_REQUEST_MINIMUM_DAYS = 2;
    const REDIRECT_BONUS_MAX_DAYS = 30;

    const STATUS_SIGNED_UP = 'signed_up';
    const STATUS_ON_RECEPTION = 'came_to_reception';
    const STATUS_ON_APPOINTMENT = 'came_to_doctor';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DELETED = 'deleted';
    const STATUS_DIDNT_COME = 'didnt_come';
    const STATUS_AMBULANCE_EN_ROUTE = 'ambulance_en_route';
    const STATUS_AMBULANCE_CALL_TRANSFERRED = 'ambulance_call_transferred';

    /**
     * @var array
     */
    protected $fillable = [
        'date',
        'start',
        'end',
        'is_first',
        'patient_id',
        'card_specialization_id',
        'doctor_id',
        'workspace_doctor_id',
        'doctor_type',
        'operator_id',
        'clinic_id',
        'specialization_id',
        'appointment_status_id',
        'status_reason_id',
        'status_reason_comment',
        'comment',
        'source_id',
        'is_deleted',
        'appointment_delete_reason_id',
        'delete_reason_comment',
        'workspace_id',
        'treatment_course_id',
        'discount_card_id',
        'insurance_policy_id',
        'legal_entity_id',
        'services',
        'created_by_patient',
        'do_not_take_payment',
        'ambulance_call'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'created_by_patient' => 'boolean',
        'do_not_take_payment' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'call_request',
        'analysis_results',
        'appointment_services',
    ];

    protected $cascade_delete = [
        'note'
    ];

    /**
     * @var mixed
     */
    public $surgeryEmployeesToSave = null;

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->creator = Auth::user()->getEmployeeModel();
        });

        static::created(function ($model) {
        });

        static::saving(function ($model) {
            if ($model->is_deleted) {
                $model->appointment_status_id = static::getStatusDeleted();
            }

            if ($model->appointment_status_id === static::getStatusSignedUp() && $model->patientCameToReception()) {
                $model->appointment_status_id = static::getStatusOnReception();
            }

            $model->restoreAssignmentsIfNotUsable();
        });

        static::updated(function ($model) {
            $model->updateRelatedEntities();
        });

        static::created(function ($model) {
            $model->attachAnalysisResults();
        });

        static::saved(function ($model) {
            $model->updateCacheValidity();
            $model->saveSurgeryEmployees();
        });
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
     * Related creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'creator_id');
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
     * Related treatment course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function treatment_course()
    {
        return $this->belongsTo(TreatmentCourse::class, 'treatment_course_id');
    }

    /**
     * Related cache validity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cache_validity()
    {
        return $this->hasOne(CacheValidity::class, 'patient_id', 'patient_id');
    }

    /**
     * Get card record initiated treatment course
     *
     * @return mixed
     */
    public function getTreatmentInitialCardRecordAttribute()
    {
        return $this->card_records()
            ->where('recordable_type', '=', TreatmentAssignment::RELATION_TYPE)
            ->with(['recordable' => function($query) {
                $query->where('initial', '=', 1);
            }])->first();
    }

    /**
     * Get first appointment in treatment course
     *
     * @return mixed
     */
    public function getFirstAppointmentInTreatmentCourseAttribute()
    {
        if (!Auth::id()) {
            $user = User::findOrFail($this->created_by_id);
            Auth::login($user);
        }

        $repository = app(AppointmentRepository::class);
        return $repository->getFilteredQuery([
            'treatment_course' => $this->treatment_course_id,
            'is_deleted' => 0,
            'date_end' => $this->date,
            'course_paid' => 1,
        ])->first();
    }

    /**
     * Get diagnosis defined at this appointment
     *
     * @return array
     */
    public function getDiagnosisAttribute()
    {
        foreach ($this->outpatient_records as $outpatientRecord) {
            $result = $outpatientRecord->recordable->diagnosis_icd->map(function ($diagnosis) {
                return $diagnosis->description;
            })->all();
            if (!empty($outpatientRecord->recordable->diagnosis)) {
                $result[] = $outpatientRecord->recordable->diagnosis;
            }
            return $result;
        }

        return [];
    }

    /**
     * Related doctor (employee || workspace)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function doctor()
    {
        return $this->morphTo();
    }

    /**
     * Related workspace doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workspaceDoctor()
    {
        return $this->belongsTo(Employee::class, 'workspace_doctor_id');
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
     * Related card specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_specialization()
    {
        return $this->belongsTo(Specialization::class, 'card_specialization_id');
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
     * Get related patient card
     *
     * @return Patient\Card
     */
    public function getPatientCardAttribute()
    {
        return $this->patient->getClinicCard($this->clinic_id, false);
    }

    /**
     * Get card number
     *
     * @return string
     */
    public function getCardNumberAttribute()
    {
        return $this->patient ? $this->patient->getCardNumber($this->clinic_id, $this->card_specialization_id) : null;
    }

    /**
     * Get card only number
     *
     * @return string
     */
    public function getCardOnlyNumberAttribute()
    {
        $card =  $this->patient ? $this->patient->getClinicCard($this->clinic_id, false) : null;

        return $card ? $card->number : null;
    }

    /**
     * Get archived card number
     *
     * @return string
     */
    public function getArchiveCardNumberAttribute()
    {
        return $this->patient ? $this->patient->getArchivedCardNumber($this->clinic_id, $this->card_specialization_id) : null;
    }

    /**
     * Related appointment status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Appointment\Status::class, 'appointment_status_id');
    }

    /**
     * Related call request
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function call_request()
    {
        return $this->hasOne(CallRequest::class, 'appointment_id');
    }

    /**
     * Get not canceled related call request
     *
     */
    public function existing_call_request()
    {
        return $this->call_request()->where('status', '!=', CallRequest::STATUS_CANCELED);
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_results()
    {
        return $this->hasMany(Analysis\Result::class, 'appointment_id');
    }

    /**
     * Related information source
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(Patient\InformationSource::class, 'source_id');
    }

    /**
     * Previous appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prev_appointment()
    {
        return $this->belongsTo(Appointment::class, 'prev_appointment_id');
    }

    /**
     * Next appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function next_appointment()
    {
        return $this->hasOne(Appointment::class, 'prev_appointment_id');
    }

        /**
     * Amublance call
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ambulance_call()
    {
        return $this->hasOne(AmbulanceCall::class);
    }

    /**
     * Set services attribute
     *
     * @param array $list
     */
    public function setServicesAttribute($list)
    {
        $services = [];
        $currentServices = $this->appointment_services()
            ->where('is_deleted', '=', 0)
            ->with(['items' => function($query) {
                $query->with('service');
            }])
            ->get();

        foreach ((array) $list as $service) {
            $serviceModel = $this->findOrCreateService($service, $currentServices);
            if ($serviceModel !== null) {
                $services[] = $serviceModel;
            }
        }

        $this->detachRemovedServices($services);
        $this->appointment_services = $services;
    }

    /**
     * Get existing/create new appointment service
     *
     * @param array $service
     * @param \Illuminate\Support\Collection $currentServices
     *
     * @return AppointmentService
     */
    protected function findOrCreateService($service, $currentServices)
    {
        $serviceId = Arr::get($service, 'service_id');
        $containerType = Arr::get($service, 'container_type') ?: null;

        // General service
        if ($containerType === null) {
            $serviceModel = $currentServices->filter(function($service) use($serviceId) {
                return $service->container_type === null
                    && $service->service_id === $serviceId;
            })->first();
            if ($serviceModel === null) {
                $serviceModel = new AppointmentService([
                    'service_id' => $serviceId,
                ]);
            }
            return $this->updateServiceData($serviceModel, $service);
        }

        // Analysis
        if ($containerType === AppointmentService::CONTAINER_ANALYSES) {
            $items = array_filter(Arr::get($service, 'items', []), function($item) {
                return !empty($item['analysis_id']);
            });
            if (empty($items)) {
                return null;
            }
            $analysisId = Arr::get(current($items), 'analysis_id');
            $serviceModel = $this->findOrCreateAnalysisContainer($serviceId, $analysisId, $currentServices);
            $currentItems = $serviceModel->relationLoaded('items') ? $serviceModel->items : collect([]);
            $serviceModel->items = array_map(function($item) use($currentItems) {
                return $this->findOrCreateAnalysisItem($item, $currentItems);
            }, $items);
            return $this->updateServiceData($serviceModel, $service);
        }

        // Nothing else is expected for now
        return null;
    }

    /**
     * Find or create appointment service item for analyses container
     *
     * @param array $item
     * @param \Illuminate\Support\Collection $currentItems
     *
     * @return AppointmentServiceItem
     */
    protected function findOrCreateAnalysisItem($item, $currentItems)
    {
        $analysisId = $item['analysis_id'];
        $itemModel = $currentItems->filter(function($item) use($analysisId) {
            return $item->extract('service.analysis_id') === $analysisId;
        })->first();

        if ($itemModel !== null) {
            return $this->updateServiceItemData($itemModel, $item, $itemModel->service);
        }

        $itemModel = new AppointmentServiceItem();
        $assigned = $this->getAssignedAnalysis($item);
        if ($assigned !== null) {
            $requiredQuantity = Arr::get($item, 'quantity', 1);
            if ($requiredQuantity >= $assigned->quantity) {
                return $this->updateServiceItemData($itemModel, $item, $assigned);
            } else {
                $assigned->quantity = $assigned->quantity - $requiredQuantity;
                $assigned->save();
            }
        }

        return $this->updateServiceItemData($itemModel, $item, new Analysis\Result([
            'analysis_id' => $analysisId,
        ]));
    }

    /**
     * Find existing container which holds analysis or create new one
     *
     * @param int $serviceId
     * @param int $analysisId
     * @param \Illuminate\Support\Collection $currentServices
     *
     * @return AppointmentService
     */
    protected function findOrCreateAnalysisContainer($serviceId, $analysisId, $currentServices)
    {
        foreach ($currentServices as $service) {
            if ($service->container_type === AppointmentService::CONTAINER_ANALYSES) {
                foreach ($service->items as $item) {
                    if ($item->extract('service.analysis_id') === $analysisId) {
                        return $service;
                    }
                }
            }
        }
        return new AppointmentService([
            'service_id' => $serviceId,
            'container_type' => AppointmentService::CONTAINER_ANALYSES,
        ]);
    }

    /**
     * Update service data
     *
     * @param AppointmentService $service
     * @param array $data
     *
     * @return AppointmentService
     */
    protected function updateServiceData($service, $data)
    {
        $service->patient_id = $this->patient_id;
        $service->clinic_id = $this->clinic_id;
        $service->card_specialization_id = $this->card_specialization_id;
        $service->price_id = Arr::get($data, 'price_id', null);
        $service->cost = Arr::get($data, 'cost');
        $service->self_cost = Arr::get($data, 'self_cost');
        $service->quantity = Arr::get($data, 'quantity', 1);
        $service->discount = Arr::get($data, 'discount', 0);
        $service->expected_payment = Arr::get($data, 'expected_payment', null);
        $service->is_base = Arr::get($data, 'is_base') ?: false;
        $service->card_assignment_id = Arr::get($data, 'card_assignment_id', null);
        $service->treatment_assignment_id = Arr::get($data, 'treatment_assignment_id', null);
        $service->by_policy = Arr::get($data, 'by_policy', false);
        $service->franchise = Arr::get($data, 'franchise', null);
        $service->warranter = Arr::get($data, 'warranter', null);

        return $service;
    }

    /**
     * Update service item data
     *
     * @param AppointmentServiceItem $item
     * @param array $data
     * @param mixed $service
     *
     * @return AppointmentServiceItem
     */
    protected function updateServiceItemData($item, $data, $service)
    {
        if ($service instanceof Analysis\Result) {
            $item->service = $this->updateAnalysisData($service, $data);
        }

        $item->quantity = Arr::get($data, 'quantity', 1);
        $item->cost = Arr::get($data, 'cost', 0);
        $item->discount = Arr::get($data, 'discount', 0);

        return $item;
    }

    /**
     * Update analysis result data
     *
     * @param Analysis\Result $analysis
     * @param array $data
     *
     * @return Analysis\Result
     */
    protected function updateAnalysisData($analysis, $data)
    {
        $analysis->patient_id = $this->patient_id;
        $analysis->card_specialization_id = Arr::get($data, 'card_specialization_id') ?: $this->card_specialization_id;
        $analysis->appointment_id = $this->id;
        $analysis->card_assignment_id = Arr::get($data, 'card_assignment_id');
        $analysis->clinic_id = $this->clinic_id;
        $analysis->cost = Arr::get($data, 'cost', 0);
        $analysis->quantity = Arr::get($data, 'quantity', 1);
        $analysis->discount = Arr::get($data, 'discount', 0);
        $analysis->price_id = Arr::get($data, 'price_id');
        $analysis->by_policy = Arr::get($data, 'by_policy', false);
        $analysis->franchise = Arr::get($data, 'franchise');
        $analysis->warranter = Arr::get($data, 'warranter');
        $analysis->status = Arr::get($data, 'status') ?: null;
        $analysis->date_expected_pass = Arr::get($data, 'date_expected_pass') ?: $this->date;
        $analysis->date_pass = Arr::get($data, 'date_pass', null);
        $analysis->date_expected_ready = Arr::get($data, 'date_expected_ready', null);
        $analysis->date_ready = Arr::get($data, 'date_ready', null);
        $analysis->date_sent_email = Arr::get($data, 'date_sent_email', null);
        $analysis->assigner_id = Arr::get($data, 'assigner_id') ?: ($this->doctor_type === Employee::RELATION_TYPE ? $this->doctor_id : null);
        $analysis->save();

        return $analysis;
    }

    /**
     * Move unused assigned services & analyses back to assignments
     */
    public function restoreAssignmentsIfNotUsable()
    {
        if ($this->isDirty('appointment_status_id')) {
            $system_status = $this->extract('status.system_status');
            if (in_array($system_status, [self::STATUS_DIDNT_COME, self::STATUS_DELETED])) {
                foreach ($this->appointment_services as $service) {
                    if ($service->container_type === AppointmentService::CONTAINER_ANALYSES) {
                        $items = $service->items->filter(function($item) {
                            if (empty($item->service->date_pass) &&
                                $item->service->status === Analysis\Result::STATUS_ASSIGNED)
                            {
                                $analysis = $item->service;
                                AppointmentServiceItem::where('service_id', '=', $item->service_id)
                                    ->where('service_type', '=', $item->service_type)
                                    ->delete();
                                $analysis->delete();
                                return false;
                            }
                            return true;
                        });
                        $service->setRelation('items', $items);
                    } elseif ($service->restoreAssignedServices()) {
                        $service->card_assignment_id = null;
                        $service->save();
                    }
                }
            }
        }
    }

    /**
     * Load assigned analysis which is related to appointment service item
     *
     * @todo Move this logic to Analysis\ResultRepository
     * @param array $item
     *
     * @return array Analysis\Result
     */
    protected function getAssignedAnalysis($item)
    {
        $query = Analysis\Result::whereNull('appointment_id')
            ->where([
                ['analysis_id', '=', $item['analysis_id']],
                ['patient_id', '=', $this->patient_id],
            ]);

        $query->whereIn($query->qualifyColumn('clinic_id'), function($query) {
            $query->select('clinics.id')
                ->from('clinics')
                ->leftJoin('clinics AS siblings', function($join) {
                    $join->on('clinics.id', '!=', 'siblings.id')
                        ->on('clinics.group_id', '=', 'siblings.group_id');
                })
                ->where('clinics.id', '=', $this->clinic_id)
                ->orWhere('siblings.id', '=', $this->clinic_id);
        });

        if (empty($item['card_assignment_id'])) {
            $query->whereNull('card_assignment_id');
        } else {
            $query->where('card_assignment_id', '=', $item['card_assignment_id']);
        }

        return $query->first();
    }

    /**
     * Attach related analysis results after appointment is created
     */
    protected function attachAnalysisResults()
    {
        foreach ($this->appointment_services as $service) {
            if ($service->container_type === AppointmentService::CONTAINER_ANALYSES) {
                foreach ($service->items as $item) {
                    if (($result = $item->service) !== null) {
                        $result->appointment_id = $this->id;
                        $result->save();
                    }
                }
            }
        }
    }

    /**
     * Get related analysis results with status assigned, passed
     *
     * @return int
     */
    public function getNotReadyAnalysesCountAttribute()
    {
        return $this->analysis_results()
            ->whereIn('status', [Analysis\Result::STATUS_ASSIGNED, Analysis\Result::STATUS_PASSED])
            ->count();
    }

    /**
     * Related appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_services()
    {
        return $this->hasMany(AppointmentService::class, 'appointment_id');
    }

    /**
     * Related payed appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payed_services()
    {
        return $this->appointment_services()->whereHas('active_payments');
    }

    /**
     * Related payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'appointment_id');
    }

    /**
     * Related delete reason
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delete_reason()
    {
        return $this->belongsTo(DeleteReason::class, 'appointment_delete_reason_id');
    }

    /**
     * Related delete reason
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_reason()
    {
        return $this->belongsTo(Reason::class, 'status_reason_id');
    }

    /**
     * Related issued discount card
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function discount_card()
    {
        return $this->belongsTo(Patient\IssuedDiscountCard::class, 'discount_card_id');
    }

    /**
     * Related card records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function card_records()
    {
        return $this->hasMany(Patient\Card\Record::class, 'appointment_id');
    }

    /**
     * Related outpatient card_records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outpatient_records()
    {
        return $this->card_records()->where('recordable_type', OutpatientRecord::RELATION_TYPE);
    }

    /**
     * Related diary records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function diary_records()
    {
        return $this->card_records()->where('recordable_type', '=', Patient\Card\DiaryRecord::RELATION_TYPE);
    }

    /**
     * Related assignment records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignment_records()
    {
        return $this->card_records()->where('recordable_type', '=', Patient\Card\Assignment::RELATION_TYPE);
    }

    /**
     * Related consultation records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consultation_records()
    {
        return $this->card_records()->where('recordable_type', '=', Patient\Card\ConsultationRecord::RELATION_TYPE);
    }

    /**
     * Related sms reminders
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sms_reminders()
    {
        return $this->hasMany(SmsAppointmentReminder::class, 'appointment_id');
    }

    /**
     * Get appointment status id by system_status
     *
     * @param string $status
     *
     * @return mixed
     */
    public static function getStatusId($status)
    {
        $result = App::make(StatusRepository::class)->getBySystemStatus($status);
        return $result ? $result->id : null;
    }

    /**
     * Get appointment status id signed up
     *
     * @return mixed
     */
    public static function getStatusSignedUp()
    {
        return static::getStatusId(self::STATUS_SIGNED_UP);
    }

    /**
     * Get appointment status id came to reception
     *
     * @return mixed
     */
    public static function getStatusOnReception()
    {
        return static::getStatusId(self::STATUS_ON_RECEPTION);
    }

    /**
     * Get appointment status id came to reception
     *
     * @return mixed
     */
    public static function getStatusCameToDoctor()
    {
        return static::getStatusId(self::STATUS_ON_APPOINTMENT);
    }

    /**
     * Get appointment status id deleted
     *
     * @return mixed
     */
    public static function getStatusDeleted()
    {
        return static::getStatusId(self::STATUS_DELETED);
    }

    /**
     * Get appointment status ambulance call transferred
     *
     * @return mixed
     */
    public static function getStatusAmbulanceCallTransferred()
    {
        return static::getStatusId(self::STATUS_AMBULANCE_CALL_TRANSFERRED);
    }

    /**
     * Get appointment status id ambulance en route
     *
     * @return mixed
     */
    public static function getStatusAmbulanceEnRoute()
    {
        return static::getStatusId(self::STATUS_AMBULANCE_EN_ROUTE);
    }

    /**
     * Get appointment status id ambulance en route
     *
     * @return mixed
     */
    public static function getStatusCompleted()
    {
        return static::getStatusId(self::STATUS_COMPLETED);
    }

    /**
     * Check patient appointments by date, clinic_id, with status came_to_reception
     *
     * @return bool
     */
    public function patientCameToReception()
    {
        $statusId = static::getStatusOnReception();
        $repository = app(AppointmentRepository::class);
        return $repository->countByValues([
            ['id', '!=', $this->id],
            ['patient_id', '=', $this->patient_id],
            ['date', '=', $this->date],
            ['clinic_id', '=', $this->clinic_id],
            ['appointment_status_id', '=', $statusId],
        ]) !== 0;
    }

    /**
     * Related analysis results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_medicines()
    {
        return $this->hasMany(Patient\AssignedMedicine::class, 'appointment_id');
    }

    /**
     * Update entity appopintment_status_id
     *
     * @param int $statusId
     */
    public function updateStatus($statusId)
    {
        $this->appointment_status_id = $statusId;
        $this->save();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Get appointment inactive statuses id
     *
     * @return array
     */
    public static function getInactiveStatuses()
    {
        return Appointment\Status::where('service_in_cost', '=', 0)->pluck('id')->all();
    }

    /**
     * Get appointment statuses id wich services can be payed
     *
     * @return array
     */
    public static function getPayableStatuses()
    {
        return Appointment\Status::where('service_in_order', '=', 0)->pluck('id')->all();
    }

    /**
     * Detach deleted services with payments
     *
     * @param array $services
     */
    protected function detachRemovedServices($services)
    {
        $ids = array_filter(Arr::pluck($services, 'id'), function($item) {
            return $item != null;
        });

        $query = $this->appointment_services()->whereHas('payments');
        if (count($ids) !== 0) {
            $query->whereNotIn('id', $ids);
        }

        $services = $query->get();

        foreach ($services as $service) {
            $service->appointment_id = null;
            $service->is_deleted = 1;
            $service->save();
        }
    }

    /**
     * Get detail info attribute
     *
     * @return string
     */
    public function getDetailInfoAttribute()
    {
        return sprintf(
            'Карта %s: %s %s-%s',
            $this->card_specialization ? $this->card_specialization->short_name : '',
            Carbon::parse($this->date)->format('Y-m-d'),
            $this->start,
            $this->end
        );
    }

    /**
     * Get detail info attribute
     *
     * @return string
     */
    public function getPatientCardDebtAttribute()
    {
        return $this->patient->getCardServiceDebt($this->card_specialization_id);
    }

    /**
     * Get first patient appointment by card
     */
    public function getRedirectFirstVisit()
    {
        if (!Auth::id()) {
            $user = User::findOrFail($this->created_by_id);
            Auth::login($user);
        }

        $repository = app(AppointmentRepository::class);
        return $repository->getFilteredQuery([
            'skip_id' => $this->id,
            'clinic' => $this->clinic_id,
            'patient' => $this->patient_id,
            'is_first' => 1,
            'is_deleted' => 0,
            'date_end' => $this->date,
            'date_start' => Carbon::parse($this->date)->subDays(static::REDIRECT_BONUS_MAX_DAYS)->format('Y-m-d'),
            'skip_status' => static::getInactiveStatuses(),
            'card_specialization' => $this->card_specialization_id,
        ])
        ->whereHas('source.employee')
        ->with('source.employee')
        ->first();
    }

    /**
     * Get Online payments cashier ID and cashbox ID
     *
     * @return array
     */
    protected function getOnlineCashierAndCashbox(){
        $onlineCashier = Employee::getOnlinePaymentCashier();
        $serviceCashbox = $onlineCashier->cashboxes->first(function($cashbox) {
            return $cashbox->payment_method != null &&
                $cashbox->payment_method->online_payment == 1 &&
                $cashbox->payment_method->use_cash == 0;
        });

        return [
            'cashier_id' => $onlineCashier->id,
            'cashbox_id' => $serviceCashbox->id
        ];
    }

    /**
     * Check if single payment is online payment
     *
     * @param $payment
     * @param $online
     *
     * @return bool
     */
    protected function checkIfOnlinePayment($payment, $online)
    {
        if (!empty($payment) && !empty($online)) {
            return ($payment->cashier_id === $online['cashier_id'] && $payment->cashbox_id === $online['cashbox_id']);
        }
        return false;
    }

    /**
     * Check if array of payments have online payment in it
     *
     * @param $payments
     * @param $online
     *
     * Check if some of payments of this appointment were made online
     *
     * @return bool
     */
    protected function hasOnlinePayments()
    {
        foreach ($this->payments as $payment) {
            if ($payment->isOnlinePayment()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update payments in appointment services
     */
    public function updatePayments()
    {
        $paymentsToOneS = [];
        $transactOneS = config('services.one_s.enable_transaction_send') == true;

        if ($this->isDirty('patient_id')) {
            $hasOnlinePayments = $this->hasOnlinePayments();
            foreach ($this->payments as $payment) {
                if ($payment->patient_id !== $this->patient_id) {
                    $payment->patient_id = $this->patient_id;
                    $payment->save();
                    if (!$hasOnlinePayments && $transactOneS) {
                        $paymentsToOneS[] = $payment;
                    }
                }
            }
        }

        if ($this->isDirty('specialization_id') && $transactOneS) {
            $paymentsToOneS = $this->payments->all();
        }

        if (count($paymentsToOneS) !== 0) {
            SendOneSTransactions::dispatch($paymentsToOneS);
        }
    }

    /**
     * Related insurance policy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurance_policy()
    {
        return $this->belongsTo(Patient\InsurancePolicy::class, 'insurance_policy_id');
    }

    /**
     * Update related entities
     */
    public function updateRelatedEntities()
    {
        $this->updatePayments();
        $this->updateCallRequest();
        $this->updateAnalysisResults();
        $this->updateAppointmentServices();
    }

    /**
     * Update related analysis results
     */
    protected function updateAnalysisResults()
    {
        if ($this->isDirty('patient_id') || $this->isDirty('card_specialization_id')) {
            $invalid = $this->analysis_results()
                ->where(function($query) {
                    $query->where('patient_id', '!=', $this->patient_id)
                        ->orWhere('card_specialization_id', '!=', $this->card_specialization_id);
                })
                ->get();
            foreach ($invalid as $result) {
                $result->patient_id = $this->patient_id;
                $result->card_specialization_id = $this->card_specialization_id;
                $result->save();
            }
        }
    }

    /**
     * Update related appointment services
     */
    protected function updateAppointmentServices()
    {
        if ($this->isDirty('patient_id') || $this->isDirty('card_specialization_id')) {
            $invalid = $this->appointment_services()
                ->where(function($query) {
                    $query->where('patient_id', '!=', $this->patient_id)
                        ->orWhere('card_specialization_id', '!=', $this->card_specialization_id);
                })
                ->get();
            foreach ($invalid as $service) {
                $service->patient_id = $this->patient_id;
                $service->card_specialization_id = $this->card_specialization_id;
                $service->save();
            }
        }
    }

    /**
     * Update call requests
     */
    public function updateCallRequest()
    {
        if ($callRequests = $this->existing_call_request) {
            if ($callRequests->patient_id != $this->patient_id) {
                $callRequests->patient_id = $this->patient_id;
                $callRequests->save();
            }
        }
    }

    /**
     * Queue sms reminder for this appointment
     * @param $template
     * @param $scheduled_at
     * @param string $type
     */
    public function queueSmsReminder($template, $scheduled_at, $type = SmsAppointmentReminder::TYPE_AUTO)
    {
        $this->sms_reminders()->create(
            [
                'template_id' => $template->id,
                'status' => Message::STATUS_NO_DELIVERY,
                'scheduled_at' => $scheduled_at,
                'type' => $type
            ]
        );
    }

    /**
     * Clear not yet sent sms reminders for appointment
     */
    public function clearNotSentAppointmentReminders()
    {
        $this->sms_reminders()->where([
            'status' => Message::STATUS_NO_DELIVERY,
        ])->delete();
    }

    /**
     * Update new timestamp at cache_validity when appointment saved
     */
    public function updateCacheValidity()
    {
        $this->cache_validity()->updateOrCreate(
            [],
            [
                'last_appointment_action_timestamp' => Carbon::now()->timestamp
            ]
        );
    }

    /**
     * Related surgery_employees
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function surgery_employees()
    {
        return $this->belongsToMany(Employee::class, 'appointment_surgery_employees', 'appointment_id', 'employee_id')
            ->withPivot('role');
    }

    /**
     * Set surgery doctors to save
     *
     * @param mixed $value
     */
    public function setSurgeryEmployeesAttribute($value)
    {
        $this->surgeryEmployeesToSave = $value;
    }

    /**
     * Save appointment surgery employees
     */
    public function saveSurgeryEmployees() {
        if ($this->surgeryEmployeesToSave !== null) {
            $this->surgery_employees()->sync(Arr::pluck(array_map(function ($item) {
                return [
                    'id' => $item['employee_id'],
                    'data' => Arr::only($item, ['role']),
                ];
            }, $this->surgeryEmployeesToSave), 'data', 'id'));
            $this->load('surgery_employees');
        }
    }

    /**
     * Related appointment note
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function note()
    {
        return $this->hasOne(Note::class, 'appointment_id');
    }

     /**
      * Related documents
      *
      * @return \Illuminate\Database\Eloquent\Relations\HasMany
      */
    public function documents()
    {
        return $this->hasMany(AppointmentDocument::class, 'appointment_id');
    }

    /**
     * Related latest acts documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function latest_acts()
    {
        return $this->documents();
    }

    /**
     * Related latest acts documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latest_act()
    {
        return $this->hasOne(AppointmentDocument::class, 'appointment_id')
            ->where('type', '=', AppointmentDocument::DOCUMENT_ACTS)->orderBy('id', 'desc')->limit(1);
    }

    /**
     * Related latest payment documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latest_payment()
    {
        return $this->hasOne(AppointmentDocument::class, 'appointment_id')
            ->where('type', '=', AppointmentDocument::DOCUMENT_PAYMENTS)->orderBy('id', 'desc')->limit(1);
    }

    /**
     * Complex related clinic and specialization
     *
     * @return \App\V1\Repositories\Relations\ComplexHasMany
     */
    public function specializations()
    {
        return $this->hasManyComplex(\App\V1\Models\Specialization\Clinic::class, [
            ['clinic_id', '=', 'clinic_id'],
            ['specialization_id', '=', 'specialization_id'],
        ]);
    }
}
