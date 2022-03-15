<?php

namespace App\V1\Http\Controllers;

use App\V1\Http\Controllers\ApiController;
use App\V1\Http\Resources\PaymentCollection;
use App\V1\Http\Resources\PaymentResource;
use App\V1\Http\Resources\PaymentCreatedResource;
use App\V1\Contracts\Repositories\PaymentRepository;
use App\V1\Contracts\Repositories\Query\PaymentFilter;
use App\V1\Contracts\Repositories\Query\PaymentSorter;
use App\V1\Contracts\Repositories\Query\PaymentScopes;
use App\V1\Http\Requests\ListRequest;
use App\V1\Http\Requests\PaymentRequest;
use App\V1\Http\Requests\PaymentBatchRequest;
use App\V1\Http\Requests\Payment\ServiceRequest as PaymentServiceRequest;
use App\V1\Http\Requests\PaymentAppointmentMinifiedRequest;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\Payment;
use App\V1\Models\Employee\Cashbox;
use App\V1\Models\Payment\Check;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Repositories\Checkbox\ShiftRepository;
use App\V1\Services\Checkbox\UnprocessibleRequestException;
use App\V1\Services\CheckboxService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use App\V1\Exceptions\PaymentException;
use Exception;
use App\V1\Traits\Controllers\Deleteable;
use App\V1\Jobs\SendOneSTransactions;
use Illuminate\Support\Facades\Auth;
use App\V1\Exceptions\PatientDepositException;
use App\V1\Models\Service;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Contracts\Repositories\Employee\CashboxRepository;

class PaymentController extends ApiController
{
    use Deleteable;

    /**
     * @var  PaymentRepository
     */
    protected $repository;

    /**
     * PaymentController constructor
     *
     * @param  PaymentRepository $repository
     */
    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get list of entities
     *
     * @param  ListRequest $request
     * @param  PaymentFilter $filter
     * @param  PaymentSorter $sorter
     * @param  PaymentScopes $scopes
     *
     * @return  PaymentCollection
     */
    public function list(ListRequest $request, PaymentFilter $filter,
        PaymentSorter $sorter, PaymentScopes $scopes) {
        $this->authorize('list', [Payment::class, $filter]);
        $collection = $this->repository->paginate($filter, $sorter, $request->getPage(), $request->getLimit());
        $scopes->apply($collection);
        return new PaymentCollection($collection);
    }

    /**
     * Get requested entity
     *
     * @param  int $id
     * @param  PaymentScopes $scopes
     *
     * @return  PaymentResource
     */
    public function get($id, PaymentScopes $scopes)
    {
        $model = $this->repository->find($id);
        $this->authorize('get', $model);
        $scopes->apply($model);
        return new PaymentResource($model);
    }

    /**
     * Create the entity
     *
     * @param PaymentRequest $request
     * @param CheckboxService $checkboxService
     * @return PaymentResource|\Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     * @throws \App\V1\Services\Checkbox\UnprocessibleRequestException
     */
    public function create(PaymentRequest $request, CheckboxService $checkboxService)
    {
        $this->authorize('create', Payment::class);
        $model = new Payment($request->safe());

        if ($this->isFiscalCashbox($model->cashbox_id)) {
            $check = $this->createCheck();
            $model->check_id = $check->id;
        }

        $this->repository->persist($model);
        if ($model->is_deposit) {
            $this->getCheckboxCheck($model, $checkboxService);
        }

        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$model]);
        }

        return $this->respondCreated(new PaymentCreatedResource($model));
    }

    /**
     * Update the entity
     *
     * @param  PaymentRequest $request
     * @param  int $id
     *
     * @return  PaymentResource
     */
    public function update(PaymentRequest $request, $id)
    {
        $model = $this->repository->find($id);
        $this->authorize('update', $model);
        try {
            $model->fill($request->safe());
            $this->repository->persist($model);
            if (config('services.one_s.enable_transaction_send') == true) {
                SendOneSTransactions::dispatch([$model]);
            }
            return $this->respondUpdated(new PaymentResource($model));
        } catch (PatientDepositException $e) {
            return $this->respondError($e->getMessage(), [], 422);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
      /**
     * Get list of entities without pagination
     *
     * @param PaymentFilter $filter
     * @param PaymentScopes $scopes
     * @param PaymentSorter $sorter
     * @return  PaymentCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function all(PaymentFilter $filter, PaymentScopes $scopes, PaymentSorter $sorter)
    {
        $this->authorize('all', Payment::class);
        $collection = $this->repository->get($filter, $sorter);
        $scopes->apply($collection);
        return new PaymentCollection($collection);
    }

    /**
     * Process batch request
     *
     * @param PaymentBatchRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batch(PaymentBatchRequest $request,CheckboxService $checkboxService)
    {
        return $this->respondSuccess([
            'update' => $this->processBatchUpdate($request),
            'create' => $this->processBatchCreate($request,$checkboxService),
        ]);
    }

    /**
     * Process batch creates
     *
     * @param PaymentBatchRequest $request
     * @param CheckboxService $checkboxService
     *
     * @return array
     */
    protected function processBatchCreate($request,CheckboxService $checkboxService)
    {
        $results = [];
        $payments = [];

        try {
            $this->authorize('create', Payment::class);
            foreach ($request->getCreates() as $data) {
                try {
                    $model = new Payment($data);
                    $this->getCheckboxCheck($model,$checkboxService);
                    $this->repository->persist($model);
                    $model->updateServiceExpectedPayment();
                    $payments[] = $model;
                    $results[] = [
                        'created' => true,
                        'data' => new PaymentCreatedResource($model)
                    ];
                } catch (PaymentException $e) {
                    $results[] = [
                        'created' => false,
                        'error' => 400,
                    ];
                } catch (UnprocessibleRequestException $e) {
                    $results[] = [
                        'created' => false,
                        'message' => $e->getResponse(),
                        'error' => 500,
                    ];
                } catch (Exception $e) {
                    \Log::error($e->getMessage());
                    \Log::error($e->getTraceAsString());
                    $results[] = [
                        'created' => false,
                        'error' => 500,
                    ];
                }
            }
        } catch (AuthorizationException $e) {
            foreach ($request->getCreates() as $data) {
                $results[] = [
                    'created' => false,
                    'error' => 403,
                ];
            }
        }

        foreach ($results as $result) {
            $results = $this->addCheckField($results);
        }

        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch($payments);
        }
        return $results;
    }

    /**
     * Process batch updates
     *
     * @param PaymentBatchRequest $request
     *
     * @return array
     */
    protected function processBatchUpdate($request)
    {
        $results = [];
        $paymetsToCheckUpdate = [];
        $payments = [];

        foreach ($request->getUpdates() as $id => $data) {
            try {
                $model = $this->repository->find($id);
                $this->authorize('update', $model);
                $model->fill($data);
                if($model->isDirty('cashbox_id')) {
                    $paymetsToCheckUpdate[] = [
                        'created' => true,
                        'data' => [
                            'id' => $model->id,
                            'type' => $model->type,
                            'cashbox_id' => $model->cashbox_id
                            ]
                    ];
                }
                $this->repository->persist($model);
                $payments[$model->id] = $model;
                $results[] = [
                    'updated' => true,
                    'data' => new PaymentResource($model)
                ];
            } catch (ModelNotFoundException $e) {
                $results[] = [
                    'updated' => false,
                    'error' => 404,
                ];
            } catch (AuthorizationException $e) {
                $results[] = [
                    'updated' => false,
                    'error' => 403,
                ];
            } catch (PaymentException $e) {
                $results[] = [
                    'created' => false,
                    'error' => 400,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'updated' => false,
                    'error' => 500,
                ];
            }
        }
        if (count($paymetsToCheckUpdate) > 0) {
            $paymentsChecks = $this->addCheckField($paymetsToCheckUpdate);
            foreach($paymentsChecks as $v) {
               $payments[$v['data']['id']]->check_id = $v['data']['check_id'];
            }
        }
        if (config('services.one_s.enable_transaction_send') == true) {
            $payments = array_values($payments);
            SendOneSTransactions::dispatch($payments);
        }
        return $results;
    }

    /**
     * Add check field to results
     *
     * @param array $results
     *
     * @return array
     */
    protected function addCheckField($payments)
    {
        $groups = $this->groupPayments($payments);
        if (empty($groups)) {
            return $payments;
        }
        $checks = $this->createChecks($groups);

        if (empty($checks)) {
            return $payments;
        }

        $results = array_map(function($result) use ($checks) {
            if(isset($result['error']) === true) {
                return $result;
            }
            foreach ($checks as $checkId => $data) {
                if (in_array($result['data']['id'], $data['payments'])) {
                    $result['data']['check_id'] = $checkId;
                    $result['data']['check'] = [
                        'id' => $checkId,
                        'created' => $data['created'],
                    ];
                }
            }
            return $result;
        }, array_filter($payments, function($payment) {
            return (isset($payment['data']) === true);
        }));

        return $results;
    }

    /**
     * Group payments by patient card
     *
     * @param array $results
     *
     * @return array
     */
    protected function groupPayments($results)
    {
        $created = [];

        foreach ($results as $result) {
            if ($result['created'] == false ||
                $result['data']['type'] == Payment::TYPE_EXPENSE
            ) {
                continue;
            }
            if (array_key_exists($result['data']['cashbox_id'], $created)) {
                array_push($created[$result['data']['cashbox_id']], $result['data']['id']);
            } else {
                $created[$result['data']['cashbox_id']] = [$result['data']['id']];
            }
        }
        return $created;
    }

    /**
     * Create non fiscal cash checks
     *
     * @param array payment $groups
     *
     * @return array
     */
    protected function createChecks($groups)
    {
        $checks = [];
        $check = null;
        foreach ($groups as $payments) {
            $paymentsList =  Payment::whereIn('id', $payments)->get();
            foreach ($paymentsList as $payment) {
                if(!$payment->check_id) {
                    $check = $this->createCheck();
                }
            }
            if($check) {
                Payment::whereIn('id', $payments)->whereNull('check_id')->update(['check_id' => $check->id]);
                $checks[$check->id] = [
                    'created' => $check->created_at,
                    'payments' => $payments,
                ];
            }
        }
        return $checks;
    }


    /**
     * Create non fiscal cash check
     *
     * @param array payment $groups
     *
     * @return Check
     */
    protected function createCheck()
    {
        $check = new Check();
        $check->save();
        return $check;
    }

    /**
     * Check if cashbox is fiscal
     *
     * @param $cashbox_id
     *
     * @return bool
     */
    protected function isFiscalCashbox($cashbox_id, $cashboxes = []) {
        if (empty($cashboxes)) {
            $cashboxes = $this->getCashNonFiscalCashboxes();
        }
        return in_array($cashbox_id, $cashboxes);
    }

    /**
     * Get cash non fiscal cashboxes
     *
     * @return array
     */
    protected function getCashNonFiscalCashboxes()
    {
        $user = Auth::user()->getEmployeeModel();
        $cashboxRepository = app(CashboxRepository::class);
        $cashboxes = $cashboxRepository->getClinicsCashNonFiscalCashboxes($user->getClinicIds(), $user->id);
        $cashboxes = $cashboxes->pluck('id')->all();
        return $cashboxes;
    }

    /**
     * Get filtered total
     *
     * @param  PaymentFilter $filter
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotal(PaymentFilter $filter) {
        $this->authorize('list', [Payment::class, $filter]);
        $total = $this->repository->getTotal($filter);
        return $this->respondSuccess(['total' => $total]);
    }

    /**
     * Update payment service attributes
     *
     * @param PaymentServiceRequest $request
     * @param int $id
     *
     * @return PaymentResource
     */
    public function updateService(PaymentServiceRequest $request, $id)
    {
        $model = $this->repository->find($id);
        $this->authorize('update', $model);
        $model->service_id = $request->input('service_id');
        $model->appointment_id = $request->input('appointment_id');
        $model->doctor_id = $request->input('doctor_id');
        $model->payment_destination_id = $request->input('payment_destination_id');
        $model->amount = $request->input('amount');
        $model->payed_amount = $request->input('payed_amount');
        $model->cashbox_id = $request->input('cashbox_id');
        $model->comment = $request->input('comment');
        if ($request->filled('created_at')) {
            $model->created_at = $request->input('created_at');
        }

        $this->repository->persist($model);
        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$model]);
        }
        return new PaymentResource($model);
    }

    /**
     * Create payments for minified appointment
     *
     * @param PaymentAppointmentMinifiedRequest $request
     */
    public function createMinified(PaymentAppointmentMinifiedRequest $request)
    {
        $attributes = $request->input();
        $availableAmount = $attributes['amount'];
        $appointmentRepository = app(AppointmentRepository::class);
        $appointment = $appointmentRepository->getFilteredQuery(['id' => $attributes['appointment_id']])
            ->with('appointment_services.service')
            ->first();

        $services = array_filter($attributes['services'], function($service) {
            return $service['service_type'] == Service::RELATION_TYPE;
        });

        $serviceIds = array_map(function($item) {
            return $item['service_id'];
        }, $services);

        $appointmentServices = $appointment->appointment_services->filter(function($item) use ($serviceIds) {
            return in_array($item->service_id, $serviceIds)
                || $item->container_type === AppointmentService::CONTAINER_ANALYSES;
        });

        if ($appointmentServices->isNotEmpty()) {
            $cashierData = Employee::getOnlineCashierData();
            if (!empty($cashierData)) {
                foreach ($appointmentServices as $service) {
                    if ($availableAmount === 0) {
                        continue;
                    }

                    $servicePayed = ($service->cost > $availableAmount) ? $availableAmount : $service->cost;
                    $availableAmount = $availableAmount - $service->cost;
                    $payment = new Payment();
                    $payment->amount = $servicePayed;
                    $payment->payed_amount = $servicePayed;
                    $payment->cashbox_id = $cashierData['service_cashbox_id'];
                    $payment->cashier_id = $cashierData['cashier_id'];
                    $payment->discount = 0;
                    $payment->service_id = $service->id;
                    $payment->doctor_id = $appointment->doctor_id;
                    $payment->clinic_id = $appointment->clinic_id;
                    $payment->patient_id = $appointment->patient_id;
                    $payment->appointment_id = $appointment->id;
                    $payment->payment_destination_id = $service->service->payment_destination_id;
                    $payment->type = Payment::TYPE_INCOME;
                    $payment->save();

                    if (config('services.one_s.enable_transaction_send') == true) {
                        SendOneSTransactions::dispatch([$payment]);
                    }
                }
            }
        }

        if ($availableAmount > 0) {
            $this->createPatientDeposit(
                $availableAmount,
                $cashierData,
                $appointment->clinic_id,
                $appointment->patient_id
            );
        }

        return $this->respondSuccess();
    }

    /**
     * Create patient deposit
     *
     * @param int|float $amount
     * @param array $cashierData
     * @param int $clinicId
     * @param int $patientId
     */
    protected function createPatientDeposit($amount, $cashierData, $clinicId, $patientId)
    {
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->payed_amount = $amount;
        $payment->discount = 0;
        $payment->cashbox_id = $cashierData['service_cashbox_id'];
        $payment->clinic_id = $clinicId;
        $payment->cashier_id = $cashierData['cashier_id'];
        $payment->patient_id = $patientId;
        $payment->type = Payment::TYPE_INCOME;
        $payment->is_deposit = 1;
        $payment->save();

        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$payment]);
        }
    }

    /**
     * get checkbox check
     *
     * @param $payment
     * @param CheckboxService $checkboxService
     * @throws \App\V1\Services\Checkbox\UnprocessibleRequestException
     */
    protected function getCheckboxCheck($payment, CheckboxService $checkboxService)
    {
        $shiftRepository = app(ShiftRepository::class);
        $cashboxRepository = app(CashboxRepository::class);
        $activeShift = null;
        $paymentCashbox = $payment->cashbox;
        $cashboxByClinic = $cashboxRepository->getCashboxByClinicAndEmployeeId($payment->clinic_id, $payment->cashier_id, $paymentCashbox->payment_method_id);

        if ($cashboxByClinic) {
            $currentCashbox = $cashboxByClinic->id;
        } else {
            $currentCashbox = $payment->cashbox_id;
        }

        if (!$this->isFiscalCashbox($currentCashbox)) {
            if ($payment->checkbox_money_reciever_id) {
                $activeShift = $shiftRepository->findActiveShift($payment->checkbox_money_reciever_id);
            } else {
                $activeShift = $shiftRepository->findActiveShift($payment->money_reciever_id);
            }

            if ($activeShift && ($payment->checkbox_money_reciever_id || $payment->money_reciever_id)) {
                $checkboxService->getCheck($payment);
            }
        }
    }
}
