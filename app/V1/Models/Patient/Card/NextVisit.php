<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\CallRequest;

class NextVisit extends BaseRecordable
{
    const RELATION_TYPE = 'next_visit';

    /**
     * @var array
     */
    protected $fillable = [
        'next_visit_date',
        'call_request_id'
    ];

    /**
     * @var string
     */
    protected $table = 'patient_next_visit';

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $cascade_delete = [
        'call_request'
    ];

    /**
     * Related call request
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function call_request()
    {
        return $this->hasOne(CallRequest::class, 'id','call_request_id');
    }

}
