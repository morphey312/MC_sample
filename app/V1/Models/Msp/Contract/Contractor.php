<?php

namespace App\V1\Models\Msp\Contract;

use App\V1\Models\BaseModel;

class Contractor extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'msp_contract_contractors';

    /**
     * @var array
     */
    protected $fillable = [
        'ehealth_id',
        'type',
	    'name',
	    'edrpou',
	    'contract_number',
	    'issued_at',
	    'expires_at',
	    'clinics',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'issued_at',
        'expires_at',
    ];

    /**
     * @var array
     */ 
    protected $casts = [
        'clinics' => 'array',
    ];
}
