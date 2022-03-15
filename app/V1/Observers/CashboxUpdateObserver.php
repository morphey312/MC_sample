<?php

namespace App\V1\Observers;

use App\V1\Models\Employee\Cashbox;
use App\V1\Events\Broadcast\CashboxUpdated;

class CashboxUpdateObserver
{
    /**
     * Listen to saved event
     * 
     * @param Cashbox $model
     */ 
    public function saved(Cashbox $model)
    {
        broadcast(new CashboxUpdated($model));
    }
}