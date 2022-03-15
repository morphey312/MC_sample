<?php

namespace App\V1\Models\InsuranceCompany;

use App\V1\Models\BaseModel;
use App\V1\Models\InsuranceCompany;
use App\V1\Models\Clinic as GenericClinic;

class Clinic extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'insurance_company_clinics';

    /**
     * @var array
     */
    protected $fillable = [
        'insurance_company_id',
        'clinic_id',
        'agreement',
        'agreement_active',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'agreement_active' => 'boolean',
    ];

    /**
     * Related insurance company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurance_company()
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }
    
    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(GenericClinic::class, 'clinic_id');
    }
}
