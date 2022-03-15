<?php

namespace App\V1\Models\Clinic;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Clinic;

class BonusNorm extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    /**
     * Specifying table name
     * 
     * @var string
     */
    protected $table= 'clinic_bonus_norms';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'clinic_id',
        'appointment_norm',
        'income_norm',
        'night_repeated_patient',
        'day_repeated_patient',
        'night_post_call',
        'day_post_call',
        'mistakes_norm',
        'evaluation_norm',
        'rate_minimum',
        'rate_medium',
        'rate_maximum',
    ];

    /**
     * @var array
     */ 
    protected $casts = [
        'appointment_norm' => 'float',
        'income_norm' => 'float',
        'evaluation_norm' => 'float',
        'rate_minimum' => 'float',
        'rate_medium' => 'float',
        'rate_maximum' => 'float',
    ];

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}