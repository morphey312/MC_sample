<?php

namespace App\V1\Observers;

use App\V1\Models\PersonalTask;
use App\V1\Events\Broadcast\NewPersonalTask;

class PersonalTaskObserver
{
    /**
     * Listen to created event
     * 
     * @param PersonalTask $model
     */ 
    public function created(PersonalTask $model)
    {
         broadcast(new NewPersonalTask($model));
    }
}