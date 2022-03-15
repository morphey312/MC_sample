<?php

namespace App\V1\Models\Msp;

use App\V1\Models\BaseModel;

class Archive extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'msp_archives';

    /**
     * @var array
     */
    protected $fillable = [
        'place',
	    'date',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'date',
    ];
}
