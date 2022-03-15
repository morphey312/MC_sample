<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient\Card;
use App\V1\Models\Specialization as ClinicSpecialization;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Traits\Models\HasConstraint;
use Carbon\Carbon;

class Specialization extends BaseModel
{
    use HasConstraint;

    /**
     * @var array
     */
    protected $fillable = [
        'specialization_id',
    ];

    /**
     * @var string
     */
    protected $table = 'card_specializations';

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'records',
    ];

    /**
     * Related card
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(ClinicSpecialization::class, 'specialization_id');
    }

    /**
     * Related records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function records()
    {
        return $this->hasMany(Record::class, 'card_specialization_id')->orderBy('created_at', 'desc');
    }

    /**
     * Related last appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'card_records', 'card_specialization_id', 'appointment_id')
            ->distinct()
            ->orderBy('date', 'DESC')->orderBy('start', 'DESC');
    }

    /**
     * Get specialization name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->specialization ? $this->specialization->name : null;
    }

    /**
     * Get specialization short name
     *
     * @return string
     */
    public function getShortNameAttribute()
    {
        return $this->specialization ? $this->specialization->short_name : null;
    }

    /**
     * Get records on particular appointment
     *
     * @param int $appointment
     * @param \App\V1\Contracts\Repositories\Query\Patient\Card\RecordFilter $filter
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAppointmentRecords($appointment, $filter = null)
    {
        $query = $this->records()
            ->with('recordable')
            ->where('appointment_id', $appointment);

        if ($filter != null) {
            $filter->apply($query);
        }

        return $query;
    }

    /**
     * Get records in this card
     *
     * @param \App\V1\Contracts\Repositories\Query\Patient\Card\RecordFilter $filter
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRecords($filter = null)
    {
        $query = $this->records()
            ->with(['recordable', 'appointment']);

        if ($filter != null) {
            $filter->apply($query);
        }

        return $query;
    }

    /**
     * Get previous appointment
     *
     * @param Appointment $appointment
     *
     * @return Appointment
     */
    public function getPreviousAppointment($appointment, $onlyEmployee = 1)
    {
        if ($appointment) {
            $lastRecord = $this->records()
                ->whereHas('appointment', function ($query) use ($appointment, $onlyEmployee) {
                    if ($onlyEmployee === 1) {
                        $query->where('doctor_type', '=', Employee::RELATION_TYPE);
                    }
                    $query->where(function ($query) use ($appointment) {
                            $query->where('date', '<', $appointment->date)
                                ->orWhere(function ($query) use ($appointment) {
                                    $query->where('date', '=', $appointment->date)
                                        ->where('start', '<', $appointment->start);
                                });
                        })
                        ->orderBy('date', 'DESC')
                        ->orderBy('start', 'DESC');
                })->first();

            if ($lastRecord !== null) {
                $lastRecord->appointment->load('doctor');
                return $lastRecord->appointment;
            }
        }

        return null;
    }
}
