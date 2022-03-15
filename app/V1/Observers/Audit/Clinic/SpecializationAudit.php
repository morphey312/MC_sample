<?php

namespace App\V1\Observers\Audit\Clinic;

use App\V1\Models\Patient\Card\RecordTemplate;
use App\V1\Observers\Audit\BaseAudit;

class SpecializationAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'short_name',
        'service_group',
        'additional_templates',
        'genitive_name',
        'is_check_up',
        'is_non_profile_patient',
        'is_non_treatment',
        'not_show_signal_records',
        'not_use_for_new_patient_call',
        'once_in_report',
        'online_appointment',
        'order',
        'course_days',
        'status',
        'card_template_id',
        'is_real_time_appointment',
    ];

    /**
     * Format is_check_up
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsCheckUpAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_non_profile_patient
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsNonProfilePatientAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_non_treatment
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsNonTreatmentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format not_show_signal_records
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNotShowSignalRecordsAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format not_use_for_new_patient_call
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNotUseForNewPatientCallAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format once_in_report
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatOnceInReportAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format online_appointment
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatOnlineAppointmentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format order
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function formatOrderAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Format course_days
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function formatCourseDaysAttribute($value)
    {
        return (int) $value;
    }

    /**
     * Format card_template_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCardTemplateIdAttribute($value)
    {
        return $this->fetchAttribute(RecordTemplate::class, $value, 'name');
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomRelations($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomRelations($model);
    }

    /**
     * Get custom relations from specialization model
     *
     * @param \App\V1\Models\Specialization $model
     *
     * @return array
     */
    protected function getCustomRelations($model)
    {
        return [
            'additional_templates' => $this->getAdditionalTemplates($model),
        ];
    }

    /**
     * Get used additional template names
     *
     * @param \App\V1\Models\Specialization $model
     *
     * @return array
     */
    protected function getAdditionalTemplates($model)
    {
        return $model->additional_templates->map(function($specialization) {
            return $specialization->name;
        });
    }

    /**
     * Format is_real_time_appointment
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsRealTimeAppointmentAttribute($value)
    {
        return (bool) $value;
    }
}
