<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;
use App\V1\Models\Service;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\Clinic;
use App\V1\Models\Employee;
use App\V1\Models\Patient\Card\Assignment;
use App\V1\Models\Price;
use App\V1\Repositories\Patient\AssignedServiceRepository;

class AssignedService extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assigned_services';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'service_id',
        'price_id',
        'quantity',
        'is_free',
        'patient_id',
        'assigner_id',
        'clinic_id',
        'card_assignment_id',
        'assigned_quantity',
        'cost',
        'discount',
        'comment',
        'by_policy',
        'franchise',
        'warranter',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'by_policy' => 'boolean',
    ];

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
    public function service()
    {
        return $this->belongsTo(Service::class);
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
     * Related assigner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner()
    {
        return $this->belongsTo(Employee::class, 'assigner_id');
    }

    /**
     * Related assigner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    /**
     * Related card_assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_assignment()
    {
        return $this->belongsTo(Assignment::class, 'card_assignment_id');
    }

    /**
     * Get appointment_service_count attribute
     *
     * @return bool
     */
    public function getAppointmentServiceCountAttribute()
    {
        return AppointmentService::where('card_assignment_id', '=', $this->card_assignment_id)
                ->where('service_id', '=', $this->service_id)
                ->where('patient_id', '=', $this->patient_id)
                ->count();
    }

    /**
     * Get appointment card specialization id
     *
     * @return mixed
     */
    public function getAppointmentCardSpecializationAttribute()
    {
        $card_assignment = $this->card_assignment;
        if ($card_assignment && $card_assignment->card_record && $card_assignment->card_record->appointment) {
            return $card_assignment->card_record->appointment->card_specialization_id;
        }
        return null;
    }
}
