<?php

namespace App\V1\Models\Clinic;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Employee\SpecialityType;

class ServiceType extends BaseModel
{
    const RELATION_TYPE = 'clinic_service_type';
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Specifying table name
     * 
     * @var string
     */
    protected $table= 'clinic_service_types';

    /**
     * @var array
     */
    protected $fillable = [
        'clinic_id',
        'speciality_type_id',
        'providing_condition',
        'comment',
        'available_time',
        'not_available',
        'is_active',
    ];

    /**
     * @var array
     */ 
    protected $casts = [
        'available_time' => 'object',
        'not_available' => 'array',
        'active_in_ehealth' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'sent_to_ehealth_at',
    ];

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
     * Related speciality type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function speciality_type()
    {
        return $this->belongsTo(SpecialityType::class, 'speciality_type_id');
    }
}
