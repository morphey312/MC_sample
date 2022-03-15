<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class Msp extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    use HasConstraint;

    const RELATION_TYPE = 'msp';
    const NO_ACCREDITATION = 'no_accreditation';

    /**
     * @var string
     */
    protected $table = 'msp';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'edrpou',
        'address',
        'phone',
        'additional_phone',
        'email',
        'website',
        'receiver_funds_code',
        'beneficiary',
        'owner_position',
        'accreditation_category',
        'accreditation_issued_date',
        'accreditation_expiry_date',
        'accreditation_order_no',
        'accreditation_order_date',
        'license_type',
        'license_number',
        'license_issued_by',
        'license_issued_date',
        'license_expiry_date',
        'license_active_from_date',
        'license_subject',
        'license_order_no',
        'archives',
        'checking_account',
        'bank',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'accreditation_issued_date',
        'accreditation_expiry_date',
        'accreditation_order_date',
        'license_issued_date',
        'license_expiry_date',
        'license_active_from_date',
        'sent_to_ehealth_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'address' => 'object',
        'edr_data' => 'object',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'employees',
        'clinics',
        'contracts'
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $owner = $model->owner;
            if ($owner->msp_id != $model->id) {
                $owner->msp_id = $model->id;
                $owner->save();
            }
        });
    }


    /**
     * Related archives
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function archives()
    {
        return $this->hasMany(Msp\Archive::class, 'msp_id');
    }

    /**
     * Related owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(Employee::class, 'owner_id');
    }

    /**
     * Related employees
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'msp_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'msp_id');
    }

    /**
     * Related contracts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {
        return $this->hasMany(Msp\Contract::class, 'msp_id');
    }
}
