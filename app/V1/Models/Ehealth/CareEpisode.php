<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Patient;
use Auth;
use Carbon\Carbon;

class CareEpisode extends BaseModel
{
    const CANCEL_REASON_TYPO = "typo";
    const CANCEL_REASON_INCORRECT_PATIENT = "incorrect_patient";

    /**
     * @var string
     */
    protected $table = 'care_episodes';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'patient_id',
        'type',
        'date_start',
        'date_end',
        'close_summary',
        'cancel_summary',
        'status_reason',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->doctor = Auth::user()->getEmployeeModel();
        });

        static::updating(function ($model) {
            if ($model->status_reason === self::CANCEL_REASON_TYPO || $model->status_reason === self::CANCEL_REASON_INCORRECT_PATIENT) {
                $model->date_end = Carbon::now()->format('Y-m-d');
            }
        });
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
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
}
