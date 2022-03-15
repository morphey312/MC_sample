<?php

namespace App\V1\Models\Notification\MailingSetting;

use App\V1\Models\BaseModel;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Traits\Models\HasConstraint;

class MailingClinic extends BaseModel
{
    use HasConstraint;

    protected $table = 'notification_mailing_template_settings_clinics';

    protected $fillable = [
        'clinic_id',
        'notification_mailing_template_id',
        'active',
        'specializations'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];


    /**
     * @var array
     */
    protected $cascade_delete = [
        'specializations'
    ];

    /**
     * Related template
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(MailingTemplate::class, 'notification_mailing_template_id');
    }

    /**
     * Related template
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specializations()
    {
        return $this->hasMany(MailingSpecialization::class, 'notification_mailing_template_settings_clinic_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(\App\V1\Models\Clinic::class, 'clinic_id');
    }

}
