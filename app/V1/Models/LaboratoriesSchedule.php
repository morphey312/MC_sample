<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class LaboratoriesSchedule extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    protected $table = 'laboratories_schedule';

    protected $fillable = ['date'];
}
