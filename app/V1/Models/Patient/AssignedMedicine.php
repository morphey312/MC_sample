<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Patient;
use App\V1\Models\Clinic;
use App\V1\Models\Employee;
use App\V1\Models\Appointment;
use App\V1\Models\Specialization;
use App\V1\Models\Appointment\Service\Item as AppointmentServiceItem;
use App\V1\Traits\Models\ServicePayment;
use App\V1\Traits\Models\HasConstraint;

class AssignedMedicine extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    use HasConstraint;

    const RELATION_TYPE = 'assigned_medicine';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assigned_medicines';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'medicine_id',
        'medicine_type',
        'patient_id',
        'assigner_id',
        'card_specialization_id',
        'clinic_id',
        'appointment_id',
        'quantity',
        'is_free',
        'cost',
        'base_cost',
        'self_cost',
        'medication_duration',
        'comment',
        'by_policy',
        'franchise',
        'warranter',
        'is_apteka24',
        'apteka24_id',
        'apteka24_order_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
        'base_cost' => 'float',
        'self_cost' => 'float',
        'is_free' => 'boolean',
        'by_policy' => 'boolean',
        'is_apteka24' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'service_items',
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
     * Related medicine
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function medicine()
    {
        return $this->morphTo();
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
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related card_specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Related issued medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issued_medicines()
    {
        return $this->hasMany(IssuedMedicine::class, 'assigned_medicine_id');
    }

    /**
     * Get issued quantity attribute
     *
     * @return int
     */
    public function getIssuedQuantityAttribute()
    {
        return $this->issued_medicines()->sum('quantity');
    }

    /**
     * Get on payment quntity attribute
     *
     * @return int
     */
    public function getOnPaymentQuantityAttribute()
    {
        return $this->service_items()
            ->whereIn('appointment_service_id', function($query) {
                $query->select('appointment_services.id')
                    ->from('appointment_services')
                    ->where('appointment_services.id', '=', 'appointment_service_items.appointment_service_id')
                    ->where('issued', '=', 0);
            })->sum('quantity');
    }

    /**
     * Related issued medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function service_items()
    {
        return $this->morphMany(AppointmentServiceItem::class, 'service');
    }

    /**
     * Get on payment quntity attribute
     *
     * @return int
     */
    public function getMedicineNameAttribute()
    {
        return $this->medicine->name;
    }
}
