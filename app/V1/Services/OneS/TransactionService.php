<?php

namespace App\V1\Services\OneS;

use App\V1\Contracts\Services\OneS\TransactionService as TransactionServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\V1\Models\Payment;
use Illuminate\Support\Facades\Log;
use App\V1\Traits\Services\OneS\ResponseProcess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Exception;
use App\V1\Models\Payment\OneSDoc;
use App\V1\Models\Appointment\Service;


class TransactionService implements TransactionServiceInterface
{
    use ResponseProcess;

    /**
     * @var string log channel
     */
    protected $logChannel = 'transactionlog';

    /**
     * @var string 1c table name
     */
    protected $docTable = 'payment_1c_docs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(config('services.one_s.transaction'));
    }

    /**
     * Execute http request to 1c
     *
     * @param mixed $list
     * @throws Exception
     */
    public function execute($payments = null)
    {
        try {
            Log::channel($this->logChannel)->info("Sending payments - id: [" . implode(', ', Arr::pluck($payments, 'id')) . "]");
            $request = $this->createTransferRequest($payments);
            $response = $this->client->send($request);
            $code = (int)$response->getStatusCode();

            if ($this->codeSuccess === $code) {
                $results = $this->getDecodedBody($response);
                $this->saveDocReference($results);
                return Log::channel($this->logChannel)->info("Transfer finished: " . ($this->getTransferResult($results)));
            }

            return Log::channel($this->logChannel)->info($this->responseMessage($response));
        } catch (RequestException $e) {
            Log::channel($this->logChannel)->info($this->responseMessage($e->getResponse()));
            throw $e;
        } catch (Exception $e) {
            Log::channel($this->logChannel)->info($this->responseMessage($e->getMessage()));
            throw $e;
        }

    }

    /**
     * Get traansfer result string
     *
     * @param array $results
     *
     * @return string
     *
     */
    protected function getTransferResult($results)
    {
        $output = '';
        if (!empty($results['docs'])) {
            foreach ($results['docs'] as $doc) {
                if (isset($doc['payment_id'])) {
                    if (!empty($doc['doc_id'])) {
                        $output .= '[' . $doc['payment_id'] . ' - ' . $doc['doc_id'] . '] ';
                    } elseif (isset($doc['error'])) {
                        $output .= '[' . $doc['payment_id'] . ' - ' . $doc['error'] . '] ';
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Save payment docs
     *
     * @param array $results
     */
    protected function saveDocReference($results = [])
    {
        if (!Schema::hasTable($this->docTable) || !is_array($results) || empty($results['docs'])) {
            return;
        }

        foreach ($results['docs'] as $doc) {
            if (isset($doc['payment_id'])) {
                if (!empty($doc['doc_id'])) {
                    OneSDoc::create($doc);
                } elseif (isset($doc['error'])) {
                    Log::channel($this->logChannel)->info("Could not create document for payment id:" . $doc['payment_id'] . " - " . $doc['error']);
                    $data = $doc;
                    $data['doc_id'] = 'ERROR - ' . substr($doc['error'], 0, 240);
                    OneSDoc::create($data);
                }
            }
        }
    }

    /**
     * Get response error message
     *
     * @param Http $response
     *
     * @return string
     */
    protected function responseMessage($response)
    {
        if (is_string($response)) {
            return "Payments transfer operation failed. " . $response;
        }
        return "Payments transfer operation failed. " . $response->getEffectiveUrl() . " " . $response->getStatusCode() . "/" . $response->getReasonPhrase();
    }

    /**
     * Create command request
     *
     * @param mixed $payments
     *
     * @return HTTP request object
     */
    protected function createTransferRequest($payments)
    {
        return $this->createRequest([
            'json' => $this->prepareRows($payments),
        ]);
    }

    /**
     * Create HTTP request
     *
     * @param array $options
     * @param string $method
     * @param string $url
     *
     * @return HTTP request object
     */
    protected function createRequest($options = [], $method = 'POST', $url = '')
    {
        return $this->client->createRequest($method, $url, $options);
    }

    /**
     * Prepare payments to send
     *
     * @param mixed $payments
     *
     * @return array
     */
    protected function prepareRows($payments)
    {
        return array_map(function ($payment) {
            $payment->loadMissing([
                'patient',
                'appointment.specialization',
                'money_reciever',
                'cashier',
                'payment_destination',
                'cashbox',
                'doctor',
                'service.service',
                'clinic',
            ]);

            $patientCard = $payment->patient->getClinicCard($payment->clinic_id, false);
            $appointment = $payment->appointment;
            $service = $payment->service;
            $moneyReciever = $payment->money_reciever;

            return [
                'payment_id' => sprintf('2%06d', $payment->id),
                'date' => $payment->created_at,
                'check_id' => $payment->check_id,
                'payed_amount' => $payment->payed_amount,
                'cashier' => $payment->cashier->full_name,
                'cashier_id' => $payment->cashier_id,
                'refund' => (int)($payment->type == Payment::TYPE_EXPENSE),
                'payment_destination' => $payment->payment_destination ? $payment->payment_destination->name : Payment::PATIENT_DEPOSIT,
                'payment_method' => $payment->cashbox->name,
                'currency_code' => $payment->clinic->currency,
                'patient_id' => $payment->patient_id,
                'patient_name' => $payment->patient->full_name,
                'patient_email' => ($payment->patient->email) ? $payment->patient->email->value : '',
                'patient_phone' => ($payment->patient->primary_phone) ? $payment->patient->primary_phone->value : '',
                'clinic_id' => $payment->clinic_id,
                'clinic_name' => $payment->clinic->name,
                'money_reciever_id' => $moneyReciever ? $moneyReciever->id : null,
                'money_reciever_name' => $moneyReciever ? $moneyReciever->name : null,
                'patient_card' => $appointment ? $appointment->card_number : '',
                'patient_card_id' => $patientCard ? $patientCard->id : '',
                'doctor_id' => $payment->doctor_id,
                'doctor_name' => $payment->doctor ? $payment->doctor->full_name : '',
                'specialization_name' => ($appointment && $appointment->specialization) ? $appointment->specialization->name : '',
                'specialization_short_name' => ($appointment && $appointment->specialization) ? $appointment->specialization->short_name : '',
                'service_id' => $service ? $service->service_id : null,
                'service_name' => ($service && $service->service) ? $this->getServiceName($service, false) : (
                $payment->is_deposit != false ? Payment::DEPOSIT_SERVICE : ''
                ),
                'service_name_ua' => ($service && $service->service) ? $this->getServiceName($service, true) : (
                $payment->is_deposit != false ? Payment::DEPOSIT_SERVICE : ''
                ),
                'discount_amount' => ($payment->amount - $payment->payed_amount),
                'is_deleted' => (int)$payment->is_deleted,
                'is_deposit' => (int)$payment->is_deposit,
                'from_deposit' => (int)$payment->from_deposit,
            ];
        }, $payments);
    }

    public static function getServiceName($service, $uaPrefix = false)
    {
        if ($service->container_type === Service::CONTAINER_ANALYSES) {
            $analysis = $service->analysis_items->map(function ($item) {
                return $item->service->analysis->name;
            });
            if ($analysis->count() === 1) {
                return implode(',', $analysis->toArray());
            }
        }
        return $uaPrefix ? $service->service->name_ua : $service->service->name;

    }
}
