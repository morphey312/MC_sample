<?php

namespace App\V1\Models\Department;

use App\V1\Models\BaseModel;
use App\V1\Models\Department;

class Room extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'department_rooms';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'department_id',
        'places',
    ];

    /**
     * Related department
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Related places
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function places()
    {
        return $this->hasMany(Room\Place::class, 'room_id');
    }

    /**
     * Related occupations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function occupations()
    {
        return $this->hasMany(Room\Occupation::class, 'room_id');
    }
}
