<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Sms\Ambulance\AmbulanceCall as AmbulanceCallSms;
use Illuminate\Support\Facades\Log;
use Messenger;

class AmbulanceCall extends BaseModel
{

    const RELATION_TYPE = 'ambulance_call';

    /**
     * @var array
     */
    protected $fillable = [
        'appointment_id',
        'caller',
        'phone',
        'street',
        'house',
        'district',
        'porch',
        'flat',
        'storey',
        'reason',
        'comment',
        'call_transferred_time',
        'en_route_time',
        'arrival_time',
        'en_route_overall_minutes',
        'patient_secondary_phone',
        'is_patient',
        'patient_home_address'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'call_transferred_time' => 'datetime',
        'en_route_time' => 'datetime',
        'arrival_time' => 'datetime',
        'call_transferred_time' => 'datetime',
        'patient_secondary_phone' => 'boolean',
        'is_patient' => 'boolean',
        'patient_home_address' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();


        static::saved(function ($model) {

            $trackedFields = array_diff($model->fillable, [
                'call_transferred_time',
                'en_route_time',
                'arrival_time',
                'en_route_overall_minutes'
            ]);

            if ($model->wasRecentlyCreated === true or $model->isDirty($trackedFields)) {
                $model->sendAmbulanceSms();
            }
        });
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function sendAmbulanceSms()
    {
        if (Messenger::send(new AmbulanceCallSms($this->appointment, $this))) {
            return true;
        }
    }
}
