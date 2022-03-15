<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class CacheValidity extends BaseModel
{
    protected $table = 'cache_validity';

    protected $fillable = [
        'last_appointment_action_timestamp',
        'last_payment_action_timestamp',
        'last_document_action_timestamp',
        'patient_id'
    ];
}
