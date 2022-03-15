<?php

namespace App\V1\Models\Patient\Card;

class DiaryRecord extends BaseRecordable
{
    const RELATION_TYPE = 'diary_record';
    
    /**
     * @var array
     */
    protected $fillable = [
        'comment',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'diary_records';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
}
