<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Jobs\Ehealth\Application as ApplicationJob;
use Auth;

class Application extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_ENABLE = 'enable';
    const ACTION_DISABLE = 'disable';
    const ACTION_APPROVE = 'approve';
    const ACTION_SIGN = 'sign';
    const ACTION_TERMINATE = 'terminate';

    const STATUS_NEW = 'new';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_WAIT_AUTH = 'wait_auth';

    /**
     * @var string
     */
    protected $table = 'ehealth_applications';

    /**
     * @var array
     */
    protected $casts = [
        'request' => 'object',
        'request_data' => 'object',
        'response' => 'object',
    ];

    /**
     * Related subject
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Related ehealth user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ehealth_user()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'user_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->employee = Auth::user()->getEmployeeModel();
        });

        static::created(function ($model) {
            $model->cancelPrevious();
            ApplicationJob::dispatch($model->id);
        });
    }

    /**
     * Cancel previous application
     */
    protected function cancelPrevious()
    {
        $this->where('id', '!=', $this->id)
            ->where('subject_id', '=', $this->subject_id)
            ->where('subject_type', '=', $this->subject_type)
            ->where('action', '=', $this->action)
            ->whereIn('status', [self::STATUS_NEW, self::STATUS_WAIT_AUTH])
            ->update([
                'status' => self::STATUS_CANCELLED,
            ]);
    }
}
