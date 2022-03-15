<?php

namespace App\V1\Models\Chat\Room;

use App\V1\Models\BaseModel;


class User extends BaseModel
{   
    /**
     * @var array
     */
    protected $table = 'chat_room_users';

    /**
     * @var array
     */
    public $timestamps = false;
    
    /**
     * @var array
     */
    protected $fillable = [
        'room_id',
        'employee_id',
    ];
  
}
