<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;

class Contact extends BaseModel
{
    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    
    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'primary',
        'value',
        'comment',
        'clinic',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'patient_contacts';

     /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            $model->syncUserData();
        });
    }
    
    /**
     * Related clinic
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Related patient
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Sync user phone with patient primary phone
     */
    protected function syncUserData() 
    {
        if ($this->type === self::TYPE_PHONE && $this->primary === 1) {
            $user = $this->extract('patient.user');
            if ($user !== null && $user->phone !== $this->value) {
                $user->phone = $this->value;
                $user->save();
            }
        }
    }
}