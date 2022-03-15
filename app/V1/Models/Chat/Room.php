<?php

namespace App\V1\Models\Chat;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Chat\Message;

class Room extends BaseModel
{   
    /**
     * @var array
     */
    protected $table = 'chat_rooms';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'employees',
    ];
    
    /**
     * @var array
     */
    public $timestamps = false;
  
    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'chat_room_users', 'room_id', 'employee_id','id');
    }
    /**
     * Related messages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'room_id');
    }
}
