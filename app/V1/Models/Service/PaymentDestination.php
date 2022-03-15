<?php

namespace App\V1\Models\Service;

use App\V1\Models\BaseModel;
use App\V1\Models\Service;
use App\V1\Models\Clinic;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class PaymentDestination extends BaseModel implements SharedResourceInterface
{
    use HasConstraint, SharedResource;

    /**
     * Model table name
     */
    protected $table = 'service_payment_destinations';

    /**
     * @var array
     *
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'color',
        'payment_destination_reference_id',
        'include_in_specialization_report',
        'additional_service_mark',
        'disabled',
        'clinics',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'services',
    ];


    /**
     * @var array
     */
    protected $casts = [
        'disabled' => 'boolean',
        'include_in_specialization_report' => 'boolean'
    ];


    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'payment_destination_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_payment_destination', 'payment_destination_id', 'clinic_id');
    }
}
