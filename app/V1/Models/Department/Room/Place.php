<?php

namespace App\V1\Models\Department\Room;

use App\V1\Models\BaseModel;
use App\V1\Models\Department\Room;

class Place extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'department_places';

    /**
     * @var array
     */
    protected $fillable = [
        'number',
        'status',
        'room_id',
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
}
