<?php

namespace App\V1\Models\Notification\Setting;

use App\V1\Models\BaseModel;
use App\V1\Models\Notification\Template;
use App\V1\Traits\Models\HasConstraint;

class VoipQueue extends BaseModel
{
    use HasConstraint;

    protected $table = 'notification_template_voip_queue';

    public $timestamps  = false;

    protected $fillable = [
        'notification_template_id',
        'queue',
    ];

}
