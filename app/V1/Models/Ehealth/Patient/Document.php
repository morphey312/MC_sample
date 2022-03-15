<?php

namespace App\V1\Models\Ehealth\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Ehealth\Patient;
use App\V1\Models\FileAttachment;

class Document extends BaseModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'ehealth_patient_documents';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'number',
        'issued_at',
        'issued_by',
        'expiration_date',
        'owner_id',
        'owner_type',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'issued_at',
        'expiration_date',
    ];

    /**
     * Related ehealth patient/confidant person
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }
}
