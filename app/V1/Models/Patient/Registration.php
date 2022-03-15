<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class Registration extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const STATUS_NEW = 'new';
    const STATUS_REGISTERED = 'registered';
    const STATUS_CONFIRMED = 'confirmed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_registrations';

    /**
     * @var array
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'birthday',
        'email',
        'status',
    ];

    /**
     * Related user record
     *
     * @return \App\V1\Repositories\Relations\BelongsToBidirect
     */
    public function user()
    {
        return $this->belongsToBidirect(User::class, 'request', 'user_id');
    }
}
