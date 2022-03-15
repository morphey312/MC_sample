<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;

class ConditionRecord extends BaseModel
{
    const RELATION_TYPE = 'condition_record';

    /**
     * @var array
     */
    protected $fillable = [
        'at',
        'at2',
        'frequency',
        'temperature',
    ];

    /**
     * @var string
     */
    protected $table = 'condition_records';

    /**
     * @var bool
     */
    public $timestamps = false;
}
