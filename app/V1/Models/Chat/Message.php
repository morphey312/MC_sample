<?php

namespace App\V1\Models\Chat;

use App\V1\Models\BaseModel;
use App\V1\Models\Chat\Room;
use App\V1\Models\Employee;
use App\V1\Models\User;

class Message extends BaseModel
{   
    /**
     * @var array
     */
    protected $table = 'chat_message';
    /**
     * @var array
     */
    protected $fillable = [
        'room_id',
        'employee_id',
        'text',
    ];
    
    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function employee()
    {   
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class,'room_id','room_id');
    }
}
