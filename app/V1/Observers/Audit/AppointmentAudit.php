<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Specialization;
use App\V1\Models\Employee;
use App\V1\Models\Appointment\Status;
use App\V1\Models\Appointment\DeleteReason;
use App\V1\Models\Patient\InformationSource;
use App\V1\Models\Patient\IssuedDiscountCard;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\Patient\InsurancePolicy;
use App\V1\Models\TreatmentCourse;
use App\V1\Models\Workspace;
use App\V1\Models\Clinic;
use App\V1\Models\LegalEntity;
use App\V1\Facades\Handbook as HandbookService;
use App\V1\Models\Handbook;

class AppointmentAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'date',
        'start',
        'end',
        'is_first',
        'patient_id',
        'doctor_id',
        'card_specialization_id',
        'operator_id',
        'specialization_id',
        'appointment_status_id',
        'status_reason_id',
        'status_reason_comment',
        'comment',
        'source_id',
        'is_deleted',
        'appointment_delete_reason_id',
        'delete_reason_comment',
        'discount_card_id',
        'treatment_course_id',
        'insurance_policy_id',
        'clinic_id',
        'legal_entity_id',
        'do_not_take_payment',
    ];

    /**
     * Format is_deleted
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsDeletedAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format start
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatStartAttribute($value)
    {
        return $this->formatTime($value);
    }

    /**
     * Format end
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatEndAttribute($value)
    {
        return $this->formatTime($value);
    }

    /**
     * Format patient_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatPatientIdAttribute($value)
    {
        return $this->formatPatientName($value);
    }

    /**
     * Format card_specialization_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCardSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }

    /**
     * Format clinic_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatClinicIdAttribute($value)
    {
        return $this->fetchAttribute(Clinic::class, $value, 'name');
    }

    /**
     * Format operator_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatOperatorIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format specialization_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }

    /**
     * Format appointment_status_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatAppointmentStatusIdAttribute($value)
    {
        return $this->fetchAttribute(Status::class, $value, 'name');
    }

    /**
     * Format status_reason_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatStatusReasonIdAttribute($value)
    {
        return $this->fetchAttribute(Status\Reason::class, $value, 'name');
    }

    /**
     * Format source
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatSourceIdAttribute($value)
    {
        return $this->fetchAttribute(InformationSource::class, $value, 'name');
    }

    /**
     * Format appointment_delete_reason_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatAppointmentDeleteReasonIdAttribute($value)
    {
        return $this->fetchAttribute(DeleteReason::class, $value, 'name');
    }

    /**
     * Format discount_card_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDiscountCardIdAttribute($value)
    {
        return $this->fetchAttribute(IssuedDiscountCard::class, $value, 'card_number');
    }

    /**
     * Format treatment_course_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatTreatmentCourseIdAttribute($value)
    {
        return $this->fetchAttribute(TreatmentCourse::class, $value, 'title');
    }

    /**
     * Format insurance_policy_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatInsurancePolicyIdAttribute($value)
    {
        return $this->fetchAttribute(InsurancePolicy::class, $value, 'display_name');
    }

    /**
     * Format do_not_take_payment
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatDoNotTakePaymentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format doctor_id
     *
     * @param mixed $value
     * @param mixed $key
     * @param mixed $model
     *
     * @return string
     */
    protected function formatDoctorIdAttribute($value, $key, $model)
    {
        if ($model->doctor_type === Employee::RELATION_TYPE) {
            return $this->fetchAttribute(Employee::class, $value, 'full_name');
        } elseif ($model->doctor_type === Employee::RELATION_TYPE) {
            return $this->fetchAttribute(Workspace::class, $value, 'name');
        }
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + $this->getCustomRelations($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model)
            + $this->getCustomRelationsUpdates($model);
    }

    /**
     * Get custom attributes from user model
     *
     * @param \App\V1\Models\Appointment $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [];
/*         if ($model->appointment_services->isNotEmpty()) {
            $analysisServices = $model->appointment_services->filter(function ($service) {
                return $service->container_type === AppointmentService::CONTAINER_ANALYSES;
            });
            // $data['appointment_services'] = $this->getAppointmentServicesData($analysisServices);
        } */

        return $data;
    }

    /**
     * Get appointment services data
     *
     * @param mixed $appointment_services
     *
     * @return array
     */
    protected function getAppointmentServicesData($appointment_services)
    {
        $services = $appointment_services->map(function($service) {
            $info = [
                'name' => $service->service->name,
                'quantity' => $service->quantity,
                'cost' => $service->getCost(),
                'is_base' => (int)$service->is_base,
                'discount' => $service->discount,
                'items' => $this->getItemsInfo($service),
                'by_policy' => (int)$service->by_policy,
                'franchise' => (int)$service->franchise,
                'warranter' => $service->warranter,
            ];
            return implode(';;', $info);
        })->all();
        return $services;
    }

    /**
     * Get appointment service items info
     *
     * @param App\V1\Models\Appointment\Service $service
     *
     * @return string
     */
    protected function getItemsInfo($service)
    {
        $items = [];
/*         if ($service->container_type == AppointmentService::CONTAINER_ANALYSES && $service->items->isNotEmpty()) {
            $items = $service->items->filter(function($item) {
                return ($item->service && $item->service->analysis);
            })->map(function($item) {
                return $item->service->analysis->name . ' стоимость: ' . round($item->cost, 2) . ', количество: ' .
                    round($item->quantity) . ', скидка: ' . round($item->discount, 2) . ', дата сдачи: ' . $item->date_pass . ', по страховой: ' . (int)$item->service->by_policy .
                    ', франшиза: ' . round($item->service->franchise, 2) . ', гарант: ' . $item->service->warranter;
            })->all();
        } */
        return implode(";$", $items);
    }
    /**
     * Format legal entity
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatLegalEntityIdAttribute($value)
    {
        return $this->fetchAttribute(LegalEntity::class, $value, 'name');
    }

    /**
     * Get custom relations from appointment model
     *
     * @param \App\V1\Models\Appointment $model
     *
     * @return array
     */
    protected function getCustomRelations($model)
    {
        return [
            'surgery_employees' => $this->getSurgeryEmployees($model->surgery_employees),
        ];
    }

    /**
     * Get custom relations updates on appointment model
     *
     * @param \App\V1\Models\Appointment $model
     *
     * @return array
     */
    protected function getCustomRelationsUpdates($model)
    {
        return [
            'surgery_employees' => $this->getSurgeryEmployeesUpdates($model),
        ];
    }

    /**
     * Get surgery doctors updates
     *
     * @param $model
     *
     * @return array
     */
    protected function getSurgeryEmployeesUpdates($model)
    {
        $data = [];

        if ($model->surgeryEmployeesToSave !== null) {
            foreach ($model->surgeryEmployeesToSave as $employee) {
                $data[] = sprintf(
                    '%s - %s',
                    $this->fetchAttribute(Employee::class, $employee['employee_id'], 'full_name'),
                    HandbookService::get(Handbook::CATEGORY_SURGERY_ROLE . '.' . $employee['role'])
                );
            }
        }
        return $data;
    }

    /**
     * Get surgery doctor info
     *
     * @param collection $surgeryEmployees
     *
     * @return array
     */
    protected function getSurgeryEmployees($surgeryEmployees)
    {
        return $surgeryEmployees->map(function($employee) {
            return sprintf(
                '%s - %s',
                $employee->full_name,
                HandbookService::get(Handbook::CATEGORY_SURGERY_ROLE . '.' . $employee->pivot->role)
            );
        })->all();
    }
}
