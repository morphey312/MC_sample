<?php

namespace App\V1\Traits\Elastic\Report\Redirects;

use App\V1\Facades\ElasticsearchClient;
use App\V1\Models\Appointment;
use App\V1\Models\Payment;
use Carbon\Carbon;

trait AppointmentDocument
{
    /**
     * Get model query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return Appointment::query();
    }

    /**
     * Get document
     * 
     * @param App\V1\Models\Appointment $model
     * 
     * @return array
     */
    protected function getDocument($model)
    {
        $specialization_visit_source = $model->isInternal ? $model->getRedirectFirstVisit() : null;
        
        return [
            'id' => $model->id,
            'clinic_id' => $model->clinic_id,
            'is_internal' => $model->isInternal ?: false,
            'payment_dates' => $this->getPaymentDates($model),
            'patient_id' => $model->patient_id,
            'date' => $model->date,
            'is_first' => $model->is_first,
            'card_specialization_id' => $model->card_specialization_id,
            'specialization_id' => $model->specialization_id,
            'appointment_source_id' => $model->source_id,
            'appointment_status_id' => $model->appointment_status_id,
            'is_deleted' => $model->is_deleted,
            'doctor_id' => $model->doctor_id,
            'doctor_type' => $model->doctor_type,
            'patient_card' => [
                'number' => $model->patient_card ? $model->patient_card->number : null,
                'specializations' => $model->patient_card->specializations->map(function($card_specialization) {
                    return [
                        'specialization_id' => $card_specialization->specialization_id,
                        'specialization' => [
                            'short_name' => $card_specialization->short_name,
                        ],
                    ];
                }),
            ],
            'specialization_name' => $model->specialization->short_name,
            'services' => $model->payed_services->map(function($service) {
                return [
                    'id' => $service->id,
                    'service_id' => $service->service_id,
                    'payed' => $service->payed,
                    'specialization' => ($service->service && $service->service->specialization) ? $service->service->specialization->short_name : null,
                    'payments' => $service->active_payments->map(function($payment) {
                        return [
                            'id' => $payment->id,
                            'payed_amount' => $payment->payed_amount,
                            'date' => $this->formatDate($payment->created_at),
                            'type' => $payment->type,
                        ];
                    }),
                ];
            }),
            'redirect_source' => $model->isInternal ? $this->getAppointmentSource($model, $specialization_visit_source) : $this->getPatientSourse($model),
            'patient_visit_date' => $model->patient->first_appointment ? $model->patient->first_appointment->date : null,
            'specialization_visit_source' => $model->isInternal ? $this->getSpecializationVisitSource($specialization_visit_source) : null,
            'source_employee_id' => $model->source ? $model->source->employee_id : null,
            'skip_redirect' => ($model->isInternal && $model->source_id == null) ? $this->shouldSkipRedirect($model) : false,
        ];
    }

    /**
     * Verify model should be skipped for internal redirects
     * 
     * @return bool
     */
    protected function shouldSkipRedirect($model)
    {
        if (in_array($model->specialization_id, $this->onceAppearanceList) && ($model->specialization_id == $model->card_specialization_id)) {
            $dateFrom = Carbon::parse($model->date)->subDays(Appointment::REDIRECT_BONUS_MAX_DAYS)->format('Y-m-d');
            $count = $model->patient->visited_appointments()
                ->where('id', '!=', $model->id)
                ->where('specialization_id', '=', $model->specialization_id)
                ->where('card_specialization_id', '=', $model->specialization_id)
                ->where('clinic_id', '=', $model->clinic_id)
                ->where('date', '<=', $model->date)
                ->where('date', '>', $dateFrom)
                ->count();

            return $count > 0;    
        } 
        return false;
    }

    /**
     * Get payment dates
     * 
     * @param App\V1\Models\Appointment $model
     * 
     * @return array
     */
    protected function getPaymentDates($model)
    {
        if ($model->payed_services) {
            $payments = $model->payed_services->pluck('payments')->flatten();
            return $payments->filter(function($payment) {
                return $payment->type === Payment::TYPE_INCOME;
            })->map(function($payment) {
                return $this->formatDate($payment->created_at);
            })
            ->values()
            ->all();
        }
        return [];
    }
    
    /**
     * Get patient first visit source by specialization
     * 
     * @param App\V1\Models\Appointment $model
     * 
     * @return mixed
     */
    protected function getSpecializationVisitSource($appointment)
    {
        return $appointment ? $appointment->source_id : null;
    }

    /**
     * Get patient source
     * 
     * @param App\V1\Models\Appointment $model
     * 
     * @return mixed
     */
    protected function getPatientSourse($model)
    {
        return $model->patient->source ? $this->getSourceData($model->patient->source, $model) : null;
    }

    /**
     * 
     * @param App\V1\Models\Appointment $model
     * 
     * @return mixed
     */
    protected function getAppointmentSource($model, $specialization_visit_source)
    {
        $source = $model->source 
            ? $model->source 
            : (($specialization_visit_source && $specialization_visit_source->source) 
                ? $specialization_visit_source->source 
                : null);

        return $source ? $this->getSourceData($source, $model) : null;
    }

    /**
     * Get information source data
     * 
     * @param mixed $source
     * @param App\V1\Models\Appointment $model
     * 
     * @return array
     */
    protected function getSourceData($source, $model)
    {
        return [
            'source_id' => $source ? $source->id : null,
            'source_name' => $source ? $source->name : null,
            'employee' => $source ? [
                    'employee_id' => $source->employee ? $source->employee_id : null,
                    'full_name' => $source->employee ? $source->employee->full_name : '',
                    'positions' => $source->employee 
                        ? $source->employee->positions->map(function($position) {
                            return [
                                'name' => $position->name,
                            ];
                        }) 
                        : [],
                    'specializations' => $source->employee 
                        ? $source->employee->clinicSpecializations($model->clinic_id)->pluck('id')->all()
                        : [],
            ] : []
        ];
    }

    /**
     * Get formatted date string
     * 
     * @param string $date
     * 
     * @return string
     */
    protected function formatDate($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Create index if not exists
     */
    protected function verifyIndexExists()
    {
        if (!ElasticsearchClient::indexExists($this->indexName)) {
            ElasticsearchClient::createIndex($this->indexName);
        }
    }
}