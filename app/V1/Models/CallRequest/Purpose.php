<?php

namespace App\V1\Models\CallRequest;

use App\V1\Models\BaseModel;
use App\V1\Models\CallRequest;
use App\V1\Models\Call\Result;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class Purpose extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_request_purposes';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'auto_add',
        'manual_add',
        'auto_next_visit',
        'call_results',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'auto_add' => 'boolean',
        'manual_add' => 'boolean',
        'auto_next_visit' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'call_requests',
        'call_results',
    ];

    /**
     * Related call requests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_requests()
    {
        return $this->hasMany(CallRequest::class, 'call_request_purpose_id');
    }

    /**
     * Related call results
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function call_results()
    {
        return $this->belongsToMany(Result::class, 'call_result_call_request_purpose', 'call_request_purpose_id', 'call_result_id');
    }
}
