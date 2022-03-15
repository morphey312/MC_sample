<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;

class BaseRecordable extends BaseModel
{
    /**
     * Related record
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function record() 
    {
        return $this->morphOne(Record::class, 'recordable');
    }
}
