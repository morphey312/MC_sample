<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Models\BaseModel;
use App\V1\Models\Notification\Template;
use App\V1\Sms\Appointment\Reminders\AppointmentReminder as Reminder;
use Messenger;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class SmsAppointmentReminder extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    const TYPE_AUTO = 'auto';
    const TYPE_MANUAL = 'manual';

    const DELIVERY_STATUS_IN_QUEUE = 'IN_QUEUE';
    const DELIVERY_STATUS_PENDING = 'PENDING';
    const DELIVERY_STATUS_DELIVERED = 'DELIVERED';
    const DELIVERY_STATUS_SMS_UNDELIVERED_STATE = 'UNDELIVERED';
    const DELIVERY_STATUS_SMS_UNDELIVERED_STATE_NEW = 'SMS_UNDELIVERED_STATE';
    const DELIVERY_STATUS_EXPIRED = 'EXPIRED';
    const DELIVERY_STATUS_NOT_FOUND = 'MESSAGE_NOT_FOUND';
    const DELIVERY_STATUS_NO_MONEY = 'NO_MONEY';
    const DELIVERY_STATUS_SEND = 'SEND';

    protected $table = 'sms_reminders';

    protected $fillable = [
        'appointment_id',
        'template_id',
        'status',
        'vendor_id',
        'vendor_data',
        'scheduled_at',
        'type',
        'phone_number',
    ];

    protected $casts = [
        'vendor_data' => 'array'
    ];

    /**
     * Related channel
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /**
     * Related channel
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Send analysis result to email
     *
     * @return bool
     */
    public function sendToPatient()
    {
        if ($this->template !== null) {
            $this->status = Message::STATUS_PREPARED;
            $this->save();

            if (Messenger::sendWithTemplate(new Reminder($this->appointment, $this), $this->template)) {
                return true;
            }
        }

        return false;
    }

}
