<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\TreatmentCourse;

class TreatmentAssignment extends BaseModel
{
    const RELATION_TYPE = 'treatment_assignment';

    /**
     * Model table
     * 
     * @var string
     */ 
    protected $table = 'treatment_assignments';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'initial',
        'treatment_course_id'
    ];

    /**
     * Related appointment services
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function appointment_services()
    {
        return $this->hasMany(AppointmentService::class, 'treatment_assignment_id');
    }

    /**
     * Related analysis_results
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function treatment_course()
    {
        return $this->belongsTo(TreatmentCourse::class, 'treatment_course_id');
    }

    /**
     * Related card_record
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function card_record()
    {
        return $this->morphOne(Record::class, 'recordable');
    }
}
