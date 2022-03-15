<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee\OutclinicSpecialization;
use App\V1\Models\Specialization;

class DoctorConsultation extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'doctor_consultations';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'comment',
        'specialization_name',
        'specialization_id',
        'consultation_record_id',
        'outclinic_specialization_id'
    ];

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function outclinic_specialization()
    {
        return $this->belongsTo(OutclinicSpecialization::class, 'outclinic_specialization_id');
    }
    /**
     * Related consultation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consultation_record()
    {
        return $this->belongsTo(ConsultationRecord::class, 'consultation_record_id');
    }

    /**
     * Get name of specialization
     *
     * @return string
     */
    public function getSpecializationNameAttribute()
    {
        if ($this->specialization_id && $this->specialization) {
            return $this->specialization->i18n('name');
        }
        if ($this->outclinic_specialization_id && $this->outclinic_specialization) {
            return $this->outclinic_specialization->i18n('name');
        }
        return $this->attributes['specialization_name'];
    }
}
