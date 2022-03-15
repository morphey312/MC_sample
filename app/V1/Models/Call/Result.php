<?php

namespace App\V1\Models\Call;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Call;
use App\V1\Models\CallRequest\Purpose;
use App\V1\Traits\Models\HasConstraint;

class Result extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_results';
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'use_for_new_calls',
        'use_for_statistics',
        'use_for_is_first_patient',
        'use_for_repeated_patient',
        'use_for_unspecialized_patient',
        'use_for_not_patient',
        'esputnik_no_answer',
        'for_wait_list',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'use_for_new_calls' => 'boolean',
        'use_for_statistics' => 'boolean',
        'use_for_is_first_patient' => 'boolean',
        'use_for_repeated_patient' => 'boolean',
        'use_for_unspecialized_patient' => 'boolean',
        'use_for_not_patient' => 'boolean',
        'esputnik_no_answer' => 'boolean',
        'for_wait_list' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'calls',
        'call_request_purposes',
    ];

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'call_result_id');
    }

    /**
     * Related call request purposes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function call_request_purposes()
    {
        return $this->belongsToMany(Purpose::class, 'call_result_call_request_purpose', 'call_result_id', 'call_request_purpose_id');
    }
}
