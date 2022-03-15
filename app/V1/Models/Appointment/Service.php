<?php

namespace App\V1\Models\Appointment;

use App\V1\Contracts\Repositories\Patient\AssignedServiceRepository;
use App\V1\Jobs\Elastic\Report\CallCenter\AppointmentBonusJob;
use App\V1\Jobs\Elastic\Report\CallCenter\AppointmentSlicesJob;
use App\V1\Jobs\Elastic\Report\Income\SingleAppointmentJob as IncomeReportAppointmentJob;
use App\V1\Jobs\Elastic\Report\Redirects\SingleAppointmentJob as RedirectsReportAppointmentJob;
use App\V1\Models\BaseModel;
use App\V1\Models\Appointment;
use App\V1\Models\Appointment\Service\Item;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;
use App\V1\Models\Price;
use App\V1\Models\Service as GenericService;
use App\V1\Models\Patient\AssignedMedicine;
use App\V1\Models\Payment;
use App\V1\Models\Analysis\Result;
use App\V1\Models\Employee;
use App\V1\Models\Patient\AssignedService;
use App\V1\Models\Patient\Card\Assignment;
use App\V1\Jobs\SendOneSMedicineIssue;
use App\V1\Models\InsuranceCompany\Act\Service as ActService;
use App\V1\Models\Patient\IssuedMedicine\Document as MedicineDocument;
use App\V1\Models\Specialization;
use App\V1\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class Service extends BaseModel
{
    const RELATION_TYPE = 'appointment_service';

    const CONTAINER_MEDICINES = 'medicines';
    const CONTAINER_ANALYSES = 'analysis_results';

    /**
     * @var string
     */
    protected $table = 'appointment_services';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'appointment_id',
        'service_id',
        'patient_id',
        'clinic_id',
        'card_specialization_id',
        'price_id',
        'cost',
        'self_cost',
        'quantity',
        'expected_payment',
        'discount',
        'is_base',
        'treatment_assignment_id',
        'card_assignment_id',
        'container_type',
        'issued',
        'items',
        'not_debt',
        'by_policy',
        'franchise',
        'warranter',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'self_cost' => 'float',
        'expected_payment' => 'float',
        'is_base' => 'boolean',
        'issued' => 'boolean',
        'not_debt' => 'boolean',
        'by_policy' => 'boolean',
    ];

    /**
     * @see Masterfri\SmartRelations\SmartRelations
     */
    public function getRelationsForCascadeDelete()
    {
        return $this->container_type === null ? [] : ['items'];
    }

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->setModelAttributes();
            $model->updateAssignedServices();
        });

        static::saved(function ($model) {
            if ($model->appointment_id && $model->getOriginal('appointment_id') && $model->isDirty('appointment_id') && $model->appointment_id !== $model->getOriginal('appointment_id')) {
                $model->transferServices();
            }

            if ($model->isDirty('is_deleted') && $model->is_deleted) {
                $model->restoreAssignedServices();
                $model->getRelationsManager()->cascadeDelete();
            }
        });

        static::deleted(function ($model) {
            $model->restoreAssignedServices();
        });
    }

    /**
     * Set service attributes
     */
    public function setModelAttributes()
    {
        $this->setCost();
        $this->setInsuranceAttributes();
    }

    /**
     * Set cost by model items cost sum
     */
    public function setCost()
    {
        $this->cost = $this->getCost();
    }

    /**
     * Get service cost
     *
     * @return int
     */
    public function getCost()
    {
        if ($this->items->isEmpty()) {
            return $this->cost;
        }

        $passedItems = $this->items->filter(function($item, $key) {
            if ($item->service_type == Result::RELATION_TYPE) {
                return empty($item->service->date_pass) === false;
            }
            return true;
        });
        return $passedItems->sum('cost');
    }

    /**
     * Get service insurance attributes
     *
     * @return int
     */
    public function setInsuranceAttributes()
    {
        if ($this->items->isEmpty() || $this->isDirty('not_debt')) {
            return;
        }

        $this->by_policy = false;
        $this->franchise = 0;
        $this->warranter = '';

        $itemsToCalculate = $this->items->filter(function($item, $key) {
            if ($item->service_type == Result::RELATION_TYPE) {
                return empty($item->service->date_pass) === false ;
            }
            return true;
        });

        if ($itemsToCalculate->count() != 0 && $this->cost > 0) {
            $patientToPay = 0;

            foreach ($itemsToCalculate as $item) {
                if ($item->service->by_policy == true) {
                    $this->by_policy = true;
                    $patientToPay += round($item->cost / 100 * $item->service->franchise, 2);
                } else {
                    $patientToPay += $item->cost;
                }

                if (!empty($item->service->warranter)) {
                    $this->warranter = $item->service->warranter;
                }
            }

            if ($patientToPay > 0 && $patientToPay != $this->cost) {
                $this->franchise = $patientToPay / $this->cost * 100;
            }
        }
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
     * Related price
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(GenericService::class, 'service_id');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
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
     * Related items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'appointment_service_id');
    }

    /**
     * Related analysis items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_items()
    {
        return $this->items()->where('service_type', '=', Item::ANALYSIS_RESULT);
    }

    /**
     * Related payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'service_id');
    }

    /**
     * Related active payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function active_payments()
    {
        return $this->payments()->where('is_deleted', '=', 0);
    }

    /**
     * Related insurance act service
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function act_service()
    {
        return $this->hasOne(ActService::class, 'service_id');
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
     * Related active payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getPayedAttribute()
    {
        $activePayments = $this->active_payments;

        if ($activePayments->isEmpty()) {
            return 0;
        }

        return ($activePayments->where('type', '=', Payment::TYPE_INCOME)->sum('amount'))
            - ($activePayments->where('type', '=', Payment::TYPE_EXPENSE)->sum('amount'));
    }

    /**
     * Update medicine issued quantity
     */
    public function updateMedicineIssuedQuantity()
    {
        if ($this->issued == false) {
            return;
        }
        $medicines = $this->items->filter(function ($medicine) {
            return $medicine->service_type === AssignedMedicine::RELATION_TYPE && $medicine->quantity > 0;
        });

        $document = MedicineDocument::create([
            'appointment_service_id' => $this->id
        ]);

        $medicines->each(function($medicine) use ($document) {
            $medicine->service->issued_medicines()->create([
                'quantity' => $medicine->quantity,
                'medicine_document_id' => $document->id,
            ]);
        });
        if (config('services.one_s.enable_issue_send') == true) {
            SendOneSMedicineIssue::dispatch($this, Auth::user()->getEmployeeModel(), $document);
        }
    }

    /**
     * Get items with assigned medicines
     *
     * @return mixed
     */
    public function getAssignedMedicineItems($relations = ['service'])
    {
        return $this->items()->with($relations)->where('service_type', '=', AssignedMedicine::RELATION_TYPE)->get();
    }

    /**
     * Get items with assigned analyses
     * @return mixed
     */
    protected function getAssignedAnalysesItems()
    {
        return $this->items()->with('service')->where('service_type', '=', Result::RELATION_TYPE)->get();
    }

    /**
     * Get assigner_id attribute
     *
     * @return mixed
     */
    public function getAssignerIdAttribute()
    {
        if ($this->container_type == static::CONTAINER_MEDICINES) {
            $medicines = $this->getAssignedMedicineItems();
            if ($medicines->isNotEmpty()) {
                $medicine = $medicines->first(function($medicine) {
                    return $medicine->service != null && $medicine->service->assigner_id != null;
                });
                return $medicine ? $medicine->service->assigner_id : null;
            }
        }

        $appointment = $this->appointment()->with(['doctor', 'patient'])->first();

        if ($appointment && $appointment->doctor instanceof Employee) {
            return $appointment->doctor_id;
        } else {
            if ($this->card_assignment_id != null) {
                if ($this->container_type == static::CONTAINER_ANALYSES) {
                    $analyses = $this->getAssignedAnalysesItems();
                    if ($analyses->isNotEmpty()) {
                        $analysis = $analyses->first(function($item) {
                            return $item->service && $item->service->assigner_id !== null;
                        });
                        return $analysis ? $analysis->service->assigner_id : null;
                    }
                } else {
                    $assignedService = $this->getRelatedAssignedService();
                    return $assignedService ? $assignedService->assigner_id : null;
                }
            } else if ($appointment && $appointment->doctor_type == Workspace::RELATION_TYPE) {
                $prevAppointment = $appointment->patient
                    ->getEmployeePrevSpecializationCardAppointment($appointment->clinic_id, $appointment->card_specialization_id, $appointment->date, $appointment->start);
                if ($prevAppointment) {
                    return $prevAppointment->doctor_id;
                }
            }
        }

        return null;
    }

    /**
     * Related assigned service
     *
     * @return \App\V1\Repositories\Relations\ComplexHasMany
     */
    public function assigned_services()
    {
        return $this->hasManyComplex(AssignedService::class, [
            ['service_id', '=', 'service_id'],
            ['card_assignment_id', '=', 'card_assignment_id'],
        ]);
    }

    /**
     * Get assigned service with same card_assignment_id
     *
     * @deprecated see assigned_services()
     * @return mixed
     */
    public function getAssignedService()
    {
        return $this->assigned_services->first();
    }

    /**
     * Get assigned service with same card_assignment_id
     * @deprecated see getAssignedService()
     * @return mixed
     */
    public function getRelatedAssignedService()
    {
        $repository = app(AssignedServiceRepository::class);
        return $repository->getAssignedService($repository->filter([
            'service' => $this->service_id,
            'card_assignment' => $this->card_assignment_id,
        ]));
    }

    /**
     * Get service name attribute
     *
     * @return mixed
     */
    public function getServiceNameAttribute()
    {
        return $this->service->name;
    }

    /**
     * Get assigner_id attribute
     *
     * @return mixed
     */
    public function getCardNumberAttribute()
    {
        if ($this->container_type == static::CONTAINER_MEDICINES) {
            $medicines = $this->getAssignedMedicineItems();
            if ($medicines->isNotEmpty()) {
                $medicine = $medicines->first(function($medicine) {
                    return $medicine->service != null && $medicine->service->card_specialization_id != null;
                });
                return $medicine ? $medicine->service->patient->getCardNumber($this->clinic_id, $medicine->service->card_specialization_id) : null;
            }
        }

        if ($this->appointment_id != null) {
            return $this->appointment->card_number;
        }
        return null;
    }

    /**
     * Update expected payment by payment amount
     * @param $amount
     */
    public function updateExpectedPayment($amount)
    {
        $expectedRest = $this->expected_payment - $amount;
        if ($expectedRest > 0) {
            $this->expected_payment = $expectedRest;
        } else {
            $this->expected_payment = 0;
        }

        $this->save();
    }

    /**
     * Update qty on assigned service once item has been saved
     */
    public function updateAssignedServices()
    {
        if ($this->card_assignment_id && $this->container_type === null) {
            $delta = 0;
            if (!$this->exists) {
                $delta = $this->quantity;
            } elseif ($this->isDirty('quantity')) {
                $delta = $this->quantity - $this->getOriginal('quantity');
            }
            if ($delta !== 0) {
                $assignment = $this->getRelatedAssignedService();
                if ($assignment !== null) {
                    if ($delta > $assignment->quantity) {
                        // Overuse, break relation
                        $this->card_assignment_id = null;
                    } else {
                        // Adjust assigned qty
                        $assignment->quantity -= $delta;
                        $assignment->save();
                    }
                }
            }
        }
    }

    /**
     * Revert qty on assigned service once item has been deleted
     */
    public function restoreAssignedServices()
    {
        if ($this->card_assignment_id && $this->container_type === null) {
            $assignment = $this->getRelatedAssignedService();
            if ($assignment !== null) {
                $assignment->quantity += $this->quantity;
                $assignment->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Get appointment service item appointment
     */
    public function getItemAppointment()
    {
        if ($this->items->isEmpty()) {
            return null;
        }

        $serviceItem = $this->items->first(function($item) {
            return $item->service->appointment_id != null;
        });
        return $serviceItem ? $serviceItem->service->appointment : null;
    }

    /**
     * Update data in elastic
     */
    public function updateElasticForAppointments()
    {
        if (config('services.elasticsearch.enable_cache')) {
            IncomeReportAppointmentJob::dispatch($this->appointment_id)->onQueue('elastic');
            IncomeReportAppointmentJob::dispatch($this->getOriginal('appointment_id'))->onQueue('elastic');
            RedirectsReportAppointmentJob::dispatch($this->appointment_id)->onQueue('elastic');
            RedirectsReportAppointmentJob::dispatch($this->getOriginal('appointment_id'))->onQueue('elastic');
            AppointmentSlicesJob::dispatch($this->appointment_id)->onQueue('elastic');
            AppointmentSlicesJob::dispatch($this->getOriginal('appointment_id'))->onQueue('elastic');
            AppointmentBonusJob::dispatch($this->appointment_id)->onQueue('elastic');
            AppointmentBonusJob::dispatch($this->getOriginal('appointment_id'))->onQueue('elastic');
        }
    }

    /**
     * change appointment in transfer service
     */
    public function transferServices()
    {
        foreach ($this->payments as $payment) {
            $payment->appointment_id = $this->appointment_id;
            $payment->save();
        }
        if ($this->container_type === self::CONTAINER_ANALYSES) {
            foreach ($this->items as $item) {
                if (($result = $item->service) !== null) {
                    $result->appointment_id = $this->appointment_id;
                    $result->card_specialization_id = $this->appointment->card_specialization_id;
                    $result->save();
                }
            }
        }
        $this->updateElasticForAppointments();
    }
}
