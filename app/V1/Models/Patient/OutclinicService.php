<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;

class OutclinicService extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outclinic_services';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'comment',
        'card_assignment_id',
    ];

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_assignment()
    {
        return $this->belongsTo(Card\Assignment::class);
    }
}
