<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class ExchangeRate extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'exchange_rates';

    /**
     * @var array
     */
    protected $fillable = [
        'value',
        'date',
        'code',
    ];
}