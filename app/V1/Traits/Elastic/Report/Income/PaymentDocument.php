<?php

namespace App\V1\Traits\Elastic\Report\Income;

use App\V1\Facades\ElasticsearchClient;
use App\V1\Models\Payment;
use App\V1\Models\Employee;
use App\V1\Models\ExchangeRate;

trait PaymentDocument
{
    /**
     * @var
     */
    protected $exchangeRates = null;

    /**
     * Get model query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return Payment::with([
            'service.appointment.doctor',
            'service.appointment.specialization',
            'service.items',
            'service.card_specialization',
            'doctor',
            'patient',
            'clinic',
        ]);
    }

    /**
     * Get document
     *
     * @param mixed $appointment
     *
     * @return array
     */
    protected function getDocument($payment)
    {
        $service = $payment->service;
        $appointment = $service ? $service->appointment : null;
        $cardSpecialization = $this->getCardSpecialization($service, $appointment);
        $date = $this->convertDate($payment->created_at);
        $patient = $payment->patient;

        return [
            'id' => $payment->id,
            'payed_amount' => $this->getPayedAmount($payment, $date),
            'base_amount' => (float) $payment->payed_amount,
            'created_at' => $date,
            'type' => $payment->type,
            'appointment_id' => $payment->appointment_id,
            'appointment_card_specialization_id' => $cardSpecialization['id'],
            'appointment_specialization_id' => $appointment ? $appointment->specialization_id : null,
            'appointment_card_specialization' => $cardSpecialization['name'],
            'appointment_specialization' => $appointment ? $appointment->specialization->short_name : null,
            'is_first' => $appointment ? $appointment->is_first : 0,
            'clinic_id' => $payment->clinic_id,
            'doctor_id' => $payment->doctor_id,
            'is_technical' => $payment->is_technical,
            'is_deposit' => $payment->is_deposit,
            'is_prepayment' => $payment->is_prepayment,
            'doctor_name' => $payment->doctor ? $payment->doctor->full_name : null,
            'is_deleted' => $payment->is_deleted,
            'is_subsidiary' => $this->isAppointmentSubsidiary($appointment, $payment),
            'service_id' => $service ? $service->service_id : null,
            'service_payment_destination_id' => $service && $service->service->payment_destination ? $service->service->payment_destination_id : null,
            'service_payment_destination' => $service && $service->service->payment_destination ? $service->service->payment_destination->name : null,
            'appointment_source_id' => $appointment ? $appointment->source_id : null,
            'patient_source_id' => $patient->source_id,
        ];
    }

    /**
     * Verify if payment appointment workspace is first income in clinic
     *
     * @param App\V1\Models\Appointment $appointment
     *
     * @return bool
     */
    protected function isAppointmentSubsidiary($appointment, $payment)
    {
        if (!$appointment || $appointment->doctor_type === Employee::RELATION_TYPE) {
            return false;
        }

        $prevAppointment = $payment->patient->visited_appointments()
            ->where('id', '!=', $appointment->id)
            ->where('date', '<=', $appointment->date)
            ->where('card_specialization_id', '=', $appointment->card_specialization_id)
            ->where('specialization_id', '=', $appointment->card_specialization_id)
            ->where('doctor_type', '=', Employee::RELATION_TYPE)
            ->orderBy('date', 'desc')
            ->readOnly()
            ->first();

        if (!$prevAppointment) {
            return false;
        }
        return $prevAppointment->clinic_id != $appointment->clinic_id;
    }

    /**
     * Get card_specialization data
     *
     * @param mixed $service
     * @param mixed $appointment
     *
     * @return array
     */
    protected function getCardSpecialization($service = null, $appointment = null)
    {
        $data = [
            'id' => null,
            'name' => null,
        ];

        if ($service) {
            if ($service->card_specialization_id != null) {
                $data['id'] = $service->card_specialization_id;
                $data['name'] = $service->card_specialization->short_name;
            } elseif ($appointment) {
                $data['id'] = $appointment->card_specialization_id;
                $data['name'] = ($appointment && $appointment->card_specialization) ? $appointment->card_specialization->short_name : null;
            }
        }
        return $data;
    }

    /**
     * Create index if not exists
     */
    protected function verifyIndexExists()
    {
        $indexName = $this->incomePaymentIndexName();
        if (!ElasticsearchClient::indexExists($indexName)) {
            ElasticsearchClient::createIndex($indexName);
        }
    }

    /**
     * Get exchange rates
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $code
     */
    protected function getRates($dateFrom, $dateTo, $code = null)
    {
        $query = ExchangeRate::where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo);

        if ($code !== null) {
            $query->where('code', '=', $code);
        }
        $results = $query->get();
        $this->exchangeRates = $results->isNotEmpty() ? $results : null;
    }

    /**
     * Get payment base currency payed_amount
     *
     * @param Payment $payment
     * @param string $date
     *
     * @return float
     */
    protected function getPayedAmount($payment, $date)
    {
        $amount = (float) $payment->payed_amount;
        if ($this->exchangeRates === null || $payment->clinic->currency === Payment::BASE_CURRENCY_CODE) {
            return $amount;
        }
        $currency = $payment->clinic->currency;
        $rate = $this->exchangeRates->first(function($row) use ($currency, $date) {
            return $row->date === $date && $row->code === $currency;
        });

        if ($rate) {
            return round($payment->payed_amount * $rate->value, 2);
        }
        return $amount;
    }
}
