<?php

namespace App\V1\Models\LegalEntity;

use App\V1\Models\BaseModel;
use App\V1\Models\LegalEntity;
use App\V1\Models\Clinic as GenericClinic;

class Clinic extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'legal_entity_clinics';

    /**
     * @var array
     */
    protected $fillable = [
        'legal_entity_id',
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
    public function legal_entity()
    {
        return $this->belongsTo(LegalEntity::class, 'legal_entity_id');
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
