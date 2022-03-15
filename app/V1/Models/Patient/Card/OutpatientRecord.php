<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\Diagnosis;
use Illuminate\Database\Eloquent\Collection;

class OutpatientRecord extends BaseRecordable
{
    const RELATION_TYPE = 'outpatient_record';

    /**
     * @var array
     */
    protected $fillable = [
        'fields',
        'diagnosis',
        'diagnosis_icd',
        'complaints',
        'template_id',
    ];

    /**
     * @var string
     */
    protected $table = 'outpatient_records';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Related ICD diagnosis
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diagnosis_icd()
    {
        return $this->belongsToMany(Diagnosis::class, 'outpatient_record_diagnosis_icd', 'record_id', 'diagnosis_id');
    }

    /**
     * Related fields
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        return $this->hasMany(OutpatientRecordField::class, 'record_id');
    }

    /**
     * Related card template
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(RecordTemplate::class, 'template_id');
    }

    /**
     * Get gather data from previous records
     *
     * @param int $cardSpecializationId
     * @param \App\V1\Models\Appointment $appointment
     * @param int $templateId
     * @param bool $isCheckUp
     * @return OutpatientRecord
     */
    public static function getPreviousData($cardSpecializationId, $appointment = null, $templateId = null)
    {
        $result = new static();
        $result->diagnosis = static::getPreviousDiagnosis($cardSpecializationId, $appointment);
        $result->complaints = static::getPreviousComplaints($cardSpecializationId, $appointment);
        $result->setRelation('diagnosis_icd', static::getPreviousDiagnosisICD($cardSpecializationId, $appointment));

        if ($templateId === null && $appointment !== null && $appointment->id) {
            $templateId = static::getRecentTemplate($appointment->id);
        }

        if ($templateId !== null) {
            $result->setRelation('fields', static::getPreviousFieldsData($cardSpecializationId, $templateId, $appointment));
            $result->template = $templateId;
        }

        return $result;
    }

    /**
     * Setup query for getting previous data
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $cardSpecializationId
     * @param \App\V1\Models\Appointment $appointment
     */
    protected static function setupPrevQuery($query, $cardSpecializationId, $appointment)
    {
        $query
            ->join('card_records', function($join) use($cardSpecializationId) {
                $join->on('card_records.recordable_id', '=', 'outpatient_records.id')
                    ->where('card_records.recordable_type', '=', self::RELATION_TYPE)
                    ->where('card_records.card_specialization_id', '=', $cardSpecializationId);
            })
            ->join('appointments', function($join) use($appointment) {
                $join->on('card_records.appointment_id', '=', 'appointments.id');
                if ($appointment) {
                    $join->whereRaw('(appointments.date < ? OR appointments.date = ? AND appointments.start < ?)', [
                        $appointment->date,
                        $appointment->date,
                        $appointment->start,
                    ]);
                }
            })
            ->orderBy('appointments.date', 'desc')
            ->orderBy('appointments.start', 'desc')
            ->orderBy('outpatient_records.id', 'desc');
    }

    /**
     * Get previous diagnosis
     *
     * @param int $cardSpecializationId
     * @param \App\V1\Models\Appointment $appointment
     *
     * @return string
     */
    public static function getPreviousDiagnosis($cardSpecializationId, $appointment = null)
    {
        $query = self::select('outpatient_records.*')->whereNotNull('diagnosis');
        self::setupPrevQuery($query, $cardSpecializationId, $appointment);
        $result = $query->first();
        return $result ? $result->diagnosis : null;
    }

    /**
     * Get previous complaints
     *
     * @param int $cardSpecializationId
     * @param \App\V1\Models\Appointment $appointment
     *
     * @return string
     */
    public static function getPreviousComplaints($cardSpecializationId, $appointment = null)
    {
        $query = self::select('outpatient_records.*')->whereNotNull('complaints');
        self::setupPrevQuery($query, $cardSpecializationId, $appointment);
        $result = $query->first();
        return $result ? $result->complaints : null;
    }

    /**
     * Get previous diagnosis
     *
     * @param int $cardSpecializationId
     * @param \App\V1\Models\Appointment $appointment
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getPreviousDiagnosisICD($cardSpecializationId, $appointment = null)
    {
        $query = self::select('outpatient_records.*')->whereHas('diagnosis_icd');
        self::setupPrevQuery($query, $cardSpecializationId, $appointment);
        $result = $query->first();
        return $result ? $result->diagnosis_icd : collect([]);
    }

    /**
     * Get previous fields data
     *
     * @param int $cardSpecializationId
     * @param int $templateId
     * @param \App\V1\Models\Appointment $appointment
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getPreviousFieldsData($cardSpecializationId, $templateId, $appointment = null)
    {
        $query = self::join('outpatient_record_fields', function($join) use($templateId) {
            $join->on('outpatient_record_fields.record_id', '=', 'outpatient_records.id')
                ->where('outpatient_records.template_id', '=', $templateId);
        })->toBase()->select('outpatient_record_fields.*');
        self::setupPrevQuery($query, $cardSpecializationId, $appointment);
        $results = $query->cursor();
        $records = [];
        foreach ($results as $result) {
            if (!array_key_exists($result->field_id, $records)) {
                $field = new OutpatientRecordField();
                $field->setRawAttributes((array) $result);
                $records[$result->field_id] = $field;
            }
        }
        return new Collection(array_values($records));
    }

    /**
     * Get most recent card template
     *
     * @param int $cardSpecializationId
     *
     * @return int
     */
    public static function getRecentTemplate($appointmentId)
    {
        $query = self::select('outpatient_records.template_id')
            ->join('card_records', function($join) use($appointmentId) {
                $join->on('card_records.recordable_id', '=', 'outpatient_records.id')
                    ->where('card_records.recordable_type', '=', self::RELATION_TYPE)
                    ->where('card_records.appointment_id', '=', $appointmentId);
            })
            ->orderBy('card_records.id', 'desc');

        $result = $query->first();
        return $result ? $result->template_id : null;
    }
}
