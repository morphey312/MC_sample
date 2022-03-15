<?php

namespace App\V1\Observers;

use App\V1\Models\Handbook as HandbookModel;
use Handbook;

class HandbookObserver
{
    /**
     * Listen to saved event
     * 
     * @param HandbookModel $model
     */ 
    public function saved(HandbookModel $model)
    {
        Handbook::flushCache();
    }
    
    /**
     * Listen to deleted event
     * 
     * @param HandbookModel $model
     */
    public function deleted(HandbookModel $model)
    {
        Handbook::flushCache();
    }
}