<?php

namespace App\V1\Models\Call;

use App\V1\Models\BaseModel;
use App\V1\Models\Call;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class DeleteReason extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'call_delete_reasons';
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'include_to_report',
        'use_for_delete',
        'comment_required',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'include_to_report' => 'boolean',
        'use_for_delete' => 'boolean',
        'comment_required' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'calls',
    ];

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'call_delete_reason_id');
    }
}
