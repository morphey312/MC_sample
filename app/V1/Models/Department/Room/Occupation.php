<?php

namespace App\V1\Models\Department\Room;

use App\V1\Models\BaseModel;
use App\V1\Models\Department\Room;
use App\V1\Models\Department\Room\Place;
use App\V1\Models\Patient;

class Occupation extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'room_occupations';

    /**
     * @var array
     */
    protected $fillable = [
        'date_from',
        'date_to',
        'start',
        'end',
        'room_id',
        'place_id',
        'patient_id',
        'status',
    ];

    /**
     * Related room
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Related place
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
