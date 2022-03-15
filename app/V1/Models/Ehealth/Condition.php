<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\Ehealth\Encounter;
use App\V1\Models\BaseModel;
use Auth;

class Condition extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'ehealth_conditions';

    /**
     * @var array
     */
    protected $fillable = [
        'clinical_status',
        'body_sites',
        'encounter_id',
        'onset_date',
        'asserted_date',
        'primary_source',
        'severity',
        'verification_status',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'daonset_datete',
        'daonset_datete',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'body_sites' => 'array',
        'primary_source' => 'boolean',
    ];

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }
}
