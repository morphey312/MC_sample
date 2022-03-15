<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient\Card\Specialization;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Repositories\Patient\AssignedServiceRepository;
use Auth;
use Carbon\Carbon;
use App\V1\Models\Patient\Card\OutpatientRecord;

class Record extends BaseModel
{
    const RELATION_TYPE = 'card_record';

    const TYPE_OUTPATIENT_RECORD = OutpatientRecord::RELATION_TYPE;
    const TYPE_CARD_ASSIGNMENT = Assignment::RELATION_TYPE;

    /**
     * @var array
     */
    protected $fillable = [
        'recordable',
        'card_specialization_id',
        'appointment_id',
    ];

    /**
     * @var string
     */
    protected $table = 'card_records';

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->relationLoaded('recordable')) {
                $recordable = $model->getRelation('recordable');
                $recordable->save();
                $model->recordable()->associate($recordable);
            }
            $model->employee = Auth::user()->getEmployeeModel();
        });

        static::saved(function ($model) {
            if ($model->recordable_type === Assignment::RELATION_TYPE) {
                $model->recordable->archiveSameAssignments($model);
            }

            if (($model->recordable_type === OutclinicProtocolRecord::RELATION_TYPE && $model->recordable->allowed_in_ok === true) || $model->recorable_type === Document::RELATION_TYPE)
                $model->updateCacheValidity();
        });

        static::deleted(function ($model) {
            if ($model->recordable) {
                $model->recordable->delete();
            }
            $model->updateCacheValidity();
        });
    }

    /**
     * Related card specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card_specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get specialization name attribute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getSpecializationNameAttribute()
    {
        $specialization = $this->extract('card_specialization.specialization');
        return $specialization ? $specialization->i18n('name') : null;
    }

    /**
     * Related record data
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recordable()
    {
        return $this->morphTo();
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


    public function visit()
    {
        return $this->belongsTo(NextVisit::class,'recordable_id');
    }

    /**
     * Set recordable
     *
     * @param BaseRecordable $recordable
     */
    public function setRecordableAttribute($recordable)
    {
        $this->setRelation('recordable', $recordable);
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getDoctorAttribute()
    {
        if ($this->employee_id) {
            return $this->employee;
        }

        return $this->extract('appointment.doctor');
    }

    /**
     * Update new timestamp at cache_validity when record saved
     */
    public function updateCacheValidity()
    {
        $this->card_specialization->card->patient->cache_validity()->updateOrCreate(
            [],
            [
                'last_document_action_timestamp' => Carbon::now()->timestamp
            ]
        );
    }
}
