<?php

namespace App\V1\Models\Patient\Card;

class ServiceRecord extends BaseRecordable
{
    const RELATION_TYPE = 'service_record';

    /**
     * @var array
     */
    protected $fillable = [
        'comment',
    ];

    /**
     * @var string
     */
    protected $table = 'service_records';

    /**
     * @var bool
     */
    public $timestamps = false;
}
