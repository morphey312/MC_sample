<?php

namespace App\V1\Models\Payment;

use App\V1\Models\BaseModel;

class OneSDoc extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_1c_docs';

    /**
     * @var array
     *
    */
    protected $fillable = [
        'payment_id',
        'doc_id',
        'doc_kind',
        'doc_num',
        'doc_date',
        'is_new',
    ];

    /**
     * @var array
     *
    */
    protected $casts = [
        'is_new' => 'boolean',
    ];
}