<?php

namespace App\V1\Observers;

use App\V1\Models\BaseModel;
use App\V1\Events\Broadcast\RecordChanged;
use Auth;

class RecordChangeObserver
{
    /**
     * Listen to saved event
     * 
     * @param BaseModel $model
     */ 
    public function saved(BaseModel $model)
    {
        broadcast(new RecordChanged([
            'saved' => $model->getClassBaseName(),
            'attributes' => $model->getBroadcastPayload(),
            'user' => Auth::id(),
        ]));
    }
    
    /**
     * Listen to deleted event
     * 
     * @param BaseModel $model
     */
    public function deleted(BaseModel $model)
    {
        broadcast(new RecordChanged([
            'deleted' => $model->getClassBaseName(),
            'attributes' => $model->getBroadcastPayload(),
            'user' => Auth::id(),
        ]));
    }
}