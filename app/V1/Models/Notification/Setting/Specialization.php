<?php

namespace App\V1\Models\Notification\Setting;

use App\V1\Models\BaseModel;

class Specialization extends BaseModel
{
    protected $table = 'notification_template_settings_clinics_specializations';

    protected $fillable = [
        'specialization_id',
        'notification_template_settings_clinic_id',
        'custom_name',
    ];

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(\App\V1\Models\Specialization::class, 'specialization_id');
    }
}
