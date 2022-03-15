<?php

namespace App\V1\Services;

use App\V1\Jobs\Checkbox\GetCheckHtmlJob;
use App\V1\Models\Checkbox\Shift;
use App\V1\Models\Payment;
use App\V1\Models\Payment\Check;
use App\V1\Repositories\Checkbox\ShiftRepository;
use App\V1\Repositories\Specialization\ClinicRepository as ClinicSpecializationRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\V1\Contracts\Services\CheckboxService as CheckboxServiceInterface;

class CheckboxService implements CheckboxServiceInterface
{
    const CONTAINER_TYPE = 'analysis_results';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $access_token;


    /**
     * Constructor
     */
    public function __construct()
    {
        $config = config('services.checkbox');
        $this->endpoint = $config['api_endpoint'];
    }


    /**
     * authorize for checkbox
     *
     * @param $login
     * @param $password
     * @param $cashboxKey
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function authorize($login,$password,$cashboxKey)
    {
        $response = $this->post('cashier/signin',
            [
                'login' => $login,
                'password' => $password,
            ]
        );
        $this->checkForErrors($response);

        $body = json_decode($response->getBody(), true);
        $this->access_token = $body['access_token'];

        return $this->openShift($cashboxKey);
    }

    /**
     * Open shift in Cashbox
     *
     * @param $cashboxKey
     * @return array
     */
    public function openShift($cashboxKey)
    {
        $response = $this->post('shifts',
            [],[], [
                'Authorization' => 'Bearer ' . $this->access_token,
                'X-License-Key' => $cashboxKey
            ]);

        $this->checkForErrors($response);

        $response->access_token = $this->access_token;
        return $response;
    }

    /**
     * Close shift in Cashbox
     *
     * @param $access_token
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface
     */
    public function closeShift($access_token)
    {
        $response = $this->post('shifts/close',
            [],[], [
                'Authorization' => 'Bearer ' . $access_token,
            ]);
        $this->checkForErrors($response);

        return $response;
    }

    /**
     * get Checkbox Check
     *
     * @param $payment
     * @return void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getCheck($payment)
    {
        if (!$payment->is_prepayment) {
            if ($payment->checkbox_money_reciever_id) {
                $this->access_token = $this->getAccessToken($payment->checkbox_money_reciever_id);
                $cashboxId = $this->getMoneyRecieverCashbox($payment->checkbox_money_reciever_id);
            } else {
                $this->access_token = $this->getAccessToken($payment->money_reciever_id);
                $cashboxId = $this->getMoneyRecieverCashbox($payment->money_reciever_id);
            }

            $data = $this->prepareData($payment);
            $response = $this->post('receipts/sell',
                $data,[], [
                    'Authorization' => 'Bearer ' . $this->access_token,
                ]);
            $this->checkForErrors($response);
            $body = json_decode($response->getBody(), true);
            $checkId = $body['id'];
            $this->setPaymentMoneyRecieverCashbox($payment,$cashboxId);
            $paymentCheck = $payment->check_id;
            if (!$paymentCheck) {
                $check = new Check();
                $check->checkbox_check_id = $checkId;
                $check->save();
                $payment->check_id = $check->id;
                $payment->save();
                $paymentCheck = $check->id;
            } else {
                $check = Check::find($paymentCheck);
                $check->checkbox_check_id = $checkId;
                $check->save();
            }
            GetCheckHtmlJob::dispatch($paymentCheck,$checkId,$this->access_token, Auth::user()->id,false);
        }
    }

    /**
     * Set payment checkbox money reciever id
     *
     * @param $payment
     * @return void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function  setPaymentMoneyRecieverCashbox($payment, $moneyRecieverId)
    {
        $payment->money_reciever_cashbox_id = $moneyRecieverId;
        $payment->save();
    }

    /**
     * get Checkbox Check
     *
     * @param $payment
     * @return void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getPrepaymentCheck($payment)
    {
        $repository = app(ClinicSpecializationRepository::class);
        $moneyReciever = $repository->getClinic($payment->clinic_id, $payment->specialization_id)->money_reciever_id;
        $this->access_token = $this->getAccessToken($moneyReciever);
        $data = $this->preparePrepaymentData($payment);
        $response = $this->post('receipts/sell',
            $data,[], [
                'Authorization' => 'Bearer ' . $this->access_token,
            ]);
        $this->checkForErrors($response);
        $body = json_decode($response->getBody(), true);
        $checkId = $body['id'];
        $paymentCheck = $payment->payment->check_id;
        if (!$paymentCheck) {
            $check = new Check();
            $check->checkbox_check_id = $checkId;
            $check->save();
            $newPayment = Payment::find($payment->payment->id);
            $newPayment->check_id = $check->id;
            $newPayment->save();
            $paymentCheck = $check->id;
        } else {
            $check = Check::find($paymentCheck);
            $check->checkbox_check_id = $checkId;
            $check->save();
        }
        GetCheckHtmlJob::dispatch($paymentCheck,$checkId,$this->access_token, Auth::user()->id,false);
    }

    /**
     * get cashier balance
     *
     * @param $token
     * @return integer
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getCashierBalance($token)
    {
        $this->access_token = $token;

        $response = $this->get('cashier/shift',
            [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
        $this->checkForErrors($response);
        $body = json_decode($response->getBody(), true);

        return (integer) $body['balance']['balance'] / 100;
    }

    /**
     * get X report
     *
     * @param $token
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getXReport($token)
    {
        $this->access_token = $token;
        $response = $this->post('reports',
            [],[], [
                'Authorization' => 'Bearer ' . $token,
            ]);
        $this->checkForErrors($response);
        $body = json_decode($response->getBody(), true);
        $reportId = $body['id'];
        return $this->downloadReport($token,$reportId);

    }

    /**
     * get Z report
     *
     * @param $token
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getZReport($token)
    {
        $this->access_token = $token;
        $response = $this->closeShift($token);
        $this->checkForErrors($response);
        $body = json_decode($response->getBody(), true);
        $reportId = $body['z_report']['id'];
        return $this->downloadReport($token,$reportId);

    }

    /**
     * get currency check
     *
     * @param $token
     * @param $amount
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|void
     * @throws Checkbox\UnprocessibleRequestException
     */
    public function getCurrencyCheck($token,$amount)
    {
        $this->access_token = $token;
        $response = $this->post('receipts/service',
            [
                'payment' => [
                    'type' => 'CASH',
                    'value' => $amount * 100,
                ]

            ],[], [
                'Authorization' => 'Bearer ' . $token,
            ]);
        $this->checkForErrors($response);
        $body = json_decode($response->getBody(), true);
        $checkId = $body['id'];
        return $this->getCheckHtml($checkId,$token);
    }

    /**
     * get Checkbox Check HTML
     *
     * @param $checkId
     * @param $token
     * @return array
     */
    public function downloadReport($token,$reportId)
    {
        return $this->get('reports/' . $reportId . '/text',
            [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
    }

    /**
     * get Checkbox Check HTML
     *
     * @param $checkId
     * @param $token
     * @return array
     */
    public function getCheckText($checkId,$token)
    {
        return $this->get('receipts/'.$checkId.'/text',
            [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
    }

    /**
     * get Checkbox Check HTML
     *
     * @param $checkId
     * @param $token
     * @return array
     */
    public function getCheckHtml($checkId,$token)
    {
        return $this->get('receipts/'.$checkId.'/html',
            [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
    }


    /**
     * set CheckBody
     *
     * @param $paymentId
     * @param $body
     * @return array
     */
    public function setCheckBody($paymentId,$body)
    {
        $payment = Payment::find($paymentId);
        $check = Check::find($payment->check_id);
        if ($check) {
            $check->body = $body;
            $check->save();
        }
    }

    /**
     * prepare data for checkbox prepayment
     *
     * @param $payment
     * @return array
     */
    public function preparePrepaymentData($payment)
    {
        $user = Auth::user()->getEmployeeModel();
        return [
            "cashier_name" => $user->full_name,
            "goods" => [
                    [
                        'good' => [
                            'name' => $payment->service->name_ua,
                            'code' => $payment->service->clinics->first(function ($item) use ($payment) {
                                return $item->id === $payment->clinic_id;
                            })->pivot->code ?? $payment->service_id,
                            'price' => $payment->amount * 100,
                        ],
                        'quantity' => 1000,
                    ]
            ],
            "payments" => [
                [
                    'type' => $payment->payment->cashbox->payment_method->use_cash ? 'CASH' : 'CASHLESS',
                    'value' => $payment->amount * 100,
                    'label' => "Передоплата"
                ]
            ],
        ];
    }

    /**
     * prepare data for checkbox
     *
     * @param $payment
     * @return array
     */
    public function prepareData($payment)
    {
        //sleep(2);
        $user = Auth::user()->getEmployeeModel();
        return [
            "cashier_name" => $user->full_name,
            "goods" => [$this->getGoods($payment)],
            "payments" => [$this->getPayments($payment)],
        ];
    }

    /**
     * prepare data for checkbox
     *
     * @param $payment
     * @return array
     */
    public function getGoods($payment)
    {
        $analysisItem = $payment->service && $payment->service->container_type === self::CONTAINER_TYPE
            ? $payment->service->analysis_items->first()->service->analysis->name
            : null;

        if ($payment->is_deposit) {
            $code = ' ';
            $name = 'Попередня оплата за медичні послуги';
        } else {
            $code = $payment->service->service->clinics->first(function ($item) use ($payment) {
                    return $item->id === $payment->clinic_id;
                })->pivot->code ?? $payment->service->service_id;
            $name = $analysisItem ?? $payment->service->service->name_ua;
        }

        return [
            'good' => [
                'name' => $name,
                'code' => $code,
                'price' => $payment->amount * 100,
            ],
            'quantity' => 1000,
            'is_return'=> $payment->type === 'expense',
        ];
    }

    /**
     * get payments
     *
     * @param $payment
     * @return array
     */
    public function getPayments($payment)
    {
        $is_cash = $payment->cashbox &&  $payment->cashbox->payment_method ? $payment->cashbox->payment_method->use_cash : null;
        return [
            'type' => $is_cash ? 'CASH' : 'CASHLESS',
            'value' => $payment->amount * 100,
        ];
    }

    /**
     * check for
     *
     * @param $response
     * @return array
     */
    public function checkForErrors($response)
    {
        $code = $response->getStatusCode();
        if (!in_array($code, [200, 201, 202])) {
            throw new Checkbox\UnprocessibleRequestException($response->json()['message'], $code);
        }
    }
    /**
     * Send post request to Cashbox
     *
     * @param string $path
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface
     */
    protected function post($path, $data = [], $query = [], $headers = [])
    {
        return $this->doRequest('POST', $path, $data, $query, $headers);
    }

    /**
     * Send get request to Cashbox
     *
     * @param string $path
     * @param array $query
     * @param array $headers
     *
     * @return array|\GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface
     */
    protected function get($path, $query = [], $headers = [])
    {
        return $this->doRequest('GET', $path, [], $query, $headers);
    }

    /**
     * Send a request to Checkbox
     *
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     */
    protected function doRequest($method, $path, $data = [], $query = [], $headers = [])
    {
        try {
            $options = $this->setupRequestOptions($data, $query, $headers);
            $client = $this->getHttpClient();
            $request = $client->createRequest($method, $path, $options);
            return $client->send($request);
        } catch (RequestException $e) {
            $this->logError($method, $path, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get http client
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'base_url' => $this->endpoint,
                'defaults' => [
                    'timeout' => 30,
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Setup request options
     *
     * @param integer $moneyRecieverId
     *
     * @return array
     */
    protected function getAccessToken($moneyRecieverId)
    {
        $shiftRepository = app(ShiftRepository::class);
        $cashbox = $shiftRepository->findActiveShift($moneyRecieverId);
        if (!$cashbox) {
            throw new \Exception('Не найдена открытая смена');
        }

        return $cashbox->access_token;
    }

    /**
     * Setup request options
     *
     * @param integer $moneyRecieverId
     *
     * @return array
     */
    protected function getMoneyRecieverCashbox($moneyRecieverId)
    {
        $shiftRepository = app(ShiftRepository::class);
        $cashbox = $shiftRepository->findActiveShift($moneyRecieverId);
        if (!$cashbox) {
            throw new \Exception('Не найдена открытая смена');
        }

        return $cashbox->money_reciever_cashbox_id;
    }
    /**
     * Setup request options
     *
     * @param array $data
     * @param array $query
     * @param array $headers
     *
     * @return array
     */
    protected function setupRequestOptions($data, $query, $headers)
    {
        $options = [
            'exceptions' => false,
        ];

        $options['headers'] = array_merge([
            'X-Requested-With' => 'XMLHttpRequest',
        ], $headers);

        if ($data !== []) {
            $options['body'] = json_encode($data,JSON_UNESCAPED_UNICODE);
        }

        if ($query !== []) {
            $options['query'] = $query;
        }

        return $options;
    }

    /**
     * Log error message
     *
     * @param string $method
     * @param string $path
     * @param mixed $status
     * @param array $input
     * @param array $output
     */
    protected function logError($method, $path, $status, $data = null, $query = null, $response = null)
    {
        Log::error('Request error: ' . $method. ' ' . $path . ': ' . $status, [
            'data' => $data,
            'query' => $query,
            'response' => $response,
        ]);
    }

}
