<?php

namespace App\V1\Models\Patient\IssuedMedicine;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Patient\IssuedMedicine;
use App\V1\Models\Appointment\Service as AppointmentService;

class Document extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    /**
     * Specify model table
     */
    protected $table = 'issued_medicine_documents';

    /**
     * @var array
     */
    protected $fillable = [
        'appointment_service_id',
    ];

    /**
     * Related issued medicines
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function issued_medicines()
    {
        return $this->hasMany(IssuedMedicine::class, 'medicine_document_id');
    }

    /**
     * Related appointment service
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function appointment_service()
    {
        return $this->belongsTo(AppointmentService::class, 'appointment_service_id');
    }
}