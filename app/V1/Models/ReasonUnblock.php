<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class ReasonUnblock extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    //
    public $table = 'day_sheet_time_unblock_reasons';
    const RELATION_TYPE = 'reason_unblock';


    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'for_operation',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'for_operation' => 'boolean',
    ];
}
