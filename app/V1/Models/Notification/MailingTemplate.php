<?php

namespace App\V1\Models\Notification;

use App\V1\Models\Appointment\Status;
use App\V1\Models\BaseModel;
use App\V1\Models\Notification\MailingSetting\MailingClinic;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Clinic;
use App\V1\Models\Employee\Position;

class MailingTemplate extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const SEND_BY_SCHEDULE = true;
    const SCENARIO_APPOINTMENT = 'send-appointment';
    const SCENARIO_APPOINTMENT_COMPLETED_HAS_NEXT_VISIT = 'send-appointment-completed-has-next-visit';
    const SCENARIO_APPOINTMENT_REMINDER = 'send-appointment-reminder';
    const SCENARIO_CONTACTS_PRIMARY_APPOINTMENT = 'send-contacts-primary-appointment';
    const SCENARIO_MISSED_FIRST_APPOINTMENTS = 'send-missed-first-appointments';
    const SCENARIO_MISSED_NEXT_APPOINTMENTS = 'send-missed-next-appointments';
    const SCENARIO_PHONE_NOT_REACHED_CONTACTS = 'send-phone-not-reached-contacts';
    const SCENARIO_PHONE_REACHED_WITHOUT_APPOINTMENT_CONTACTS = 'send-phone-reached-without-appointment-contacts';
    const SCENARIO_CONFIRMED_DATA = 'send-confirmed-data';
    const SCENARIO_EMAIL_STATUS = 'send-email-status';


    /**
     * @var string
     */
    protected $table = 'notification_mailing_templates';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'scenario',
        'channel_id',
        'specialization_id',
        'service_id',
        'mailing_time',
        'schedule_mailing',
        'clinics',
        'statuses',
        'disabled',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'disabled' => 'boolean',
        'schedule_mailing' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $allowedTags = [
        'p', 'div', 'br', 'b', 'i', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'pre', 'ul', 'ol', 'li', 'img', 'table', 'tbody', 'tr', 'td',
    ];

    /**
     * @var array
     */
    protected $allowedStyles = [
        'text-align',
    ];

    /**
     * Related positions
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'notification_template_positions', 'template_id', 'position_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'notification_template_clinics', 'template_id', 'clinic_id');
    }

    /**
     * Related provider
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(MailingProvider::class, 'channel_id');
    }

    /**
     * Related provider
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statuses()
    {
        return $this->belongsToMany(Status\Reason::class, 'notification_mailing_template_status', 'mailing_template_id', 'status_reasons_id');
    }

    /**
     * Related settings clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings_clinics()
    {
        return $this->hasMany(MailingClinic::class, 'notification_mailing_template_id');

    }
}
