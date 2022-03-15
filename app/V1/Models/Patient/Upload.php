<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\CacheValidity;
use App\V1\Models\Patient;
use App\V1\Models\FileAttachment;
use Carbon\Carbon;

class Upload extends BaseModel
{
    const RELATION_TYPE = 'upload';

    /**
     * @var string
     */
    protected $table = 'patient_uploads';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
	    'description',
	    'file_id',
        'patient_id',
        'is_protected',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_protected' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->updateCacheValidity();
        });

        static::deleted(function ($model) {
            $model->updateCacheValidity();
        });
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Related file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(FileAttachment::class, 'file_id');
    }

    /**
     * Related cache validity
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cache_validity()
    {
        return $this->hasOne(CacheValidity::class, 'patient_id', 'patient_id');
    }

    /**
     * Update new timestamp at cache_validity when upload saved
     */
    public function updateCacheValidity()
    {
        $this->cache_validity()->updateOrCreate(
            [],
            [
                'last_document_action_timestamp' => Carbon::now()->timestamp
            ]
        );
    }
}
