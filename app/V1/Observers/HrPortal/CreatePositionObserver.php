<?php

namespace App\V1\Observers\HrPortal;

use App\V1\Jobs\HrPortal\CreateNewPosition;
use App\V1\Models\Employee\Position;

class CreatePositionObserver
{
    /**
     * Handle the position "created" event.
     *
     * @param Position $position
     * @return void
     */
    public function created(Position $position)
    {
        CreateNewPosition::dispatch($position->id, $position->name);
    }


}
