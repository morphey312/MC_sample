<?php

namespace App\V1\Models\Patient\Card;


class ManipulationRecord extends BaseRecordable
{
    const RELATION_TYPE = 'manipulation_record';

    /**
     * @var array
     */
    protected $fillable = [
        'comment',
    ];

    /**
     * @var string
     */
    protected $table = 'manipulation_records';

    /**
     * @var bool
     */
    public $timestamps = false;
}
