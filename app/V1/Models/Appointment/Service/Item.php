<?php

namespace App\V1\Models\Appointment\Service;

use App\V1\Models\BaseModel;
use App\V1\Models\Appointment\Service as AppointmentService;

class Item extends BaseModel
{
    const ANALYSIS_RESULT = 'analysis_result';
    const ASSIGNED_MEDICINE = 'assigned_medicine';
    /**
     * @var string
     */ 
    protected $table = 'appointment_service_items';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'appointment_service_id',
        'service',
        'service_id',
        'service_type',
        'quantity',
        'cost',
        'self_cost',
        'discount',
    ];

    /**
     * Related appointment
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function appointment_service()
    {
        return $this->belongsTo(AppointmentService::class, 'appointment_service_id');
    }

    /**
     * Related service
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */ 
    public function service()
    {
        return $this->morphTo();
    }

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            if ($model->service_type === self::ANALYSIS_RESULT) {
                if ($model->service->attachments()->exists() === false) {
                    $model->service->delete();
                }
            }
        });
    }
}