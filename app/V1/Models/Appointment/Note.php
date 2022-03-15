<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;

class Note extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'appointment_notes';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'appointment_id',
        'note',
        'task',
    ];

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
