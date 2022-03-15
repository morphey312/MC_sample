<?php

namespace App\V1\Models\Notification\Setting;

use App\V1\Models\BaseModel;
use App\V1\Models\Notification\Template;
use App\V1\Traits\Models\HasConstraint;

class Clinic extends BaseModel
{
    use HasConstraint;

    protected $table = 'notification_template_settings_clinics';

    protected $fillable = [
        'clinic_id',
        'notification_template_id',
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
        return $this->belongsTo(Template::class, 'notification_template_id');
    }

    /**
     * Related template
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specializations()
    {
        return $this->hasMany(Specialization::class, 'notification_template_settings_clinic_id');
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
