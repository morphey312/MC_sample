<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Patient\Card\TreatmentAssignment;
use App\V1\Traits\Models\ModelNumber;
class TreatmentCourse extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    use ModelNumber;
    
    const RELATION_TYPE = 'treatment_course';

    /**
     * @var array
     */
    protected $fillable = [
      'start',
      'end',
      'patient_id',
      'doctor_id',
      'card_specialization_id',
      'is_surgery',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_surgery' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if ($model->is_surgery) {
                $model->pickNumber();
            }
        });

        static::deleting(function ($model) {
            $model->clearConstrains();
        });
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
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }
    
    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'card_specialization_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'treatment_course_id');
    }

    /**
     * Related treatment_assignments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function treatment_assignments()
    {
        return $this->hasMany(TreatmentAssignment::class, 'treatment_course_id');
    }
    
    /**
     * Related visited appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visited_appointments()
    {
        return $this->appointments()
            ->whereIn('appointment_status_id', function($query) {
                $query->select('appointment_statuses.id')
                    ->from('appointment_statuses')
                    ->whereNotIn('system_status', [
                        Appointment::STATUS_SIGNED_UP,
                        Appointment::STATUS_ON_RECEPTION,
                        Appointment::STATUS_DELETED,
                        Appointment::STATUS_DIDNT_COME,
                    ])
                    ->orWhereNull('system_status');
            })
            ->orderBy('date', 'asc')
            ->orderBy('start', 'asc');
    }

    /**
     * Get title attribute
     * 
     * @return string
     */
    public function getTitleAttribute()
    {
        return "Курс ". ($this->specialization ? $this->specialization->name : '');
    }

    /**
     * Delete constrains
     */
    public function clearConstrains()
    {
        $assignments = $this->treatment_assignments()->with('card_record')->get();
        $assignments->each(function($assignment) {
            if ($assignment->card_record) {
                $assignment->card_record->delete();
            }
            $assignment->delete();
        });
    }

    /**
     * Related documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(TreatmentCourse\Document::class, 'treatment_course_id');
    }
}
