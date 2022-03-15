<?php

namespace App\V1\Models\Ehealth\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Ehealth\Patient;

class RelationshipDocument extends BaseModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'ehealth_patient_relationship_documents';

    /**
     * @var array
     */
    protected $fillable = [
        'ehealth_patient_id',
        'type',
        'number',
        'document_id',
        'issued_at',
        'issued_by',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'issued_at',
    ];

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'ehealth_patient_id');
    }
}
