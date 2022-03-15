<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient;
use App\V1\Models\InsuranceCompany;
use App\V1\Models\Appointment;
use App\V1\Traits\Models\HasConstraint;

class InsurancePolicy extends BaseModel
{
    use HasConstraint;

    /**
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'insurance_company_id',
        'number',
        'expires',
        'comment',
    ];

    /**
     * @var string
     */ 
    protected $table = 'patient_insurance_policies';

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'appointments',
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
     * Related insurance_company
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function insurance_company()
    {
        return $this->belongsTo(InsuranceCompany::class);
    }

    /**
     * Related appointments
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'insurance_policy_id');
    }

    /**
     * Get display name attribute
     * 
     * @return string
     */ 
    public function getDisplayNameAttribute()
    {
        return "№{$this->number} {$this->insurance_company->title}";
    }

    /**
     * Get full info attribute
     * 
     * @return string
     */ 
    public function getFullInfoAttribute()
    {
        return $this->display_name . ", Окончание действия: " . $this->expires;
    }
}
