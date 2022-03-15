<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
class EmailLog extends BaseModel
{
    const RELATION_TYPE = 'email_log';
    const TYPE_ANALYSIS = 'analysis';

    /**
     * @var string
     */
    protected $table = 'email_logs';

    protected $fillable = [
        'target_type',
        'target_id',
        'subject',
        'from',
        'to',
        'event',
        'event_data',
        'attachments'
    ];

    protected $casts = [
        'event_data' => 'array'
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
