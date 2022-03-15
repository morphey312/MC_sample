<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;

class ConsultationRecord extends BaseModel
{
    const RELATION_TYPE = 'consultation_record';
    
    /**
     * @var string
     */ 
    protected $table = 'consultation_records';

    /**
     * @var bool
     */ 
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'consultations',
    ];

    /**
     * Related record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consultations() 
    {
        return $this->hasMany(DoctorConsultation::class, 'consultation_record_id');
    }
}
