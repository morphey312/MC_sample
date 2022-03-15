<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\User as McUser;
use App\V1\Jobs\Ehealth\Application as ApplicationJob;

class User extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'ehealth_users';

    /**
     * @var array
     */
    protected $dates = [
        'token_expires',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($model) {
            Application::where('created_by_id', '=', $model->user_id)
                ->where('status', '=', Application::STATUS_WAIT_AUTH)
                ->orderBy('id')
                ->chunkById(100, function ($applications) {
                    foreach ($applications as $application) {
                        ApplicationJob::dispatch($application->id);     
                    }
                });
        });
    }

    /**
     * Related MC user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mc_user()
    {
        return $this->belongsTo(McUser::class, 'user_id');
    }
}
