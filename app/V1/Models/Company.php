<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Jobs\SetupClientJob;

class Company extends BaseModel
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_DISABLED = 'disabled';
    const STATUS_FAILED = 'failed';

    /**
     * @var array
     */
    protected $casts = [
        'config' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            SetupClientJob::dispatch($model->id);
        });
    }
}
