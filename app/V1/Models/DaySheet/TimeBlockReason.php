<?php

namespace App\V1\Models\DaySheet;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class TimeBlockReason extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    /**
     * Specify model table
     * 
     * @var string
     */
    protected $table = 'day_sheet_time_block_reasons';

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
