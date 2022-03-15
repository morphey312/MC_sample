<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\FileAttachment;

class OutclinicProtocolRecord extends BaseRecordable
{
    const RELATION_TYPE = 'outclinic_protocol_record';

    /**
     * @var string
     */
    protected $table = 'outclinic_protocol_records';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'attachments',
        'name',
        'allowed_in_ok'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Related attachments
     *
     * @return \App\V1\Repositories\Relations\FileAttachment
     */
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }
}
