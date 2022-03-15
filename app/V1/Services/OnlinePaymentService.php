<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\OnlinePaymentService as OnlinePaymentServiceInterface;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Models\Payment;
use App\V1\Models\Analysis;
use App\V1\Models\Analysis\Result as AnalysisResult;
use App\V1\Models\Service;
use App\V1\Models\Employee;
use App\V1\Models\Call\RelatedAction;
use App\V1\Models\SiteEnquiry\Service as SiteEnquiryService;
use App\V1\Models\Patient\Prepayment;
use App\V1\Models\WaitListRecord;
use App\V1\Jobs\SendOneSTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Repositories\Appointment\ServiceRepository as AppointmentServiceRepository;

class OnlinePaymentService implements OnlinePaymentServiceInterface
{
    /**
     * @var string log channel
     */
    protected $logChannel = 'onlineServicePayments';

    /**
     * @var string site enquiry services table
     */
    protected $table = 'site_enquiry_services';

    /**
     * @var App\V1\Models\SiteEnquiry
     */
    protected $enquiry = null;

    /**
     * @var array
     */
    protected $onlineData = null;

    /**
     * @var int
     */
    protected $analysisDoctorId = null;

    /**
     * @var mixed
     */
    protected $serviceRepository = null;

    /**
     * @var array
     */
    protected $createdAppointments = [];

    /**
     * @var string
     */
    protected $serviceCashboxId = null;

    /**
     * Verify if process log appointments created in clinic which not match enquiry clinic
     *
     * @return bool
     */
    protected function isForeignClinic()
    {
        if (empty($this->createdAppointments)) {
            return false;
        }

        $appointmentRepository = app(AppointmentRepository::class);
        $clinicIds = $appointmentRepository->getFilteredQuery([
            'id' => $this->createdAppointments,
            'is_deleted' => 0,
        ])->pluck('clinic_id');

        if ($clinicIds->count() === 1) {
            return !in_array($this->enquiry->clinic_id, $clinicIds->toArray());
        }

        return $clinicIds->filter(function($clinic_id) {
            return $clinic_id == $this->enquiry->clinic_id;
        })->count() === 0;
    }

    /**
     * Set refund status for enquiry services
     *
     * @param mixed $services
     */
    protected function setRefund($services)
    {
        $this->saveEnquiryServiceAttributes($services, [
            'refund_status' => SiteEnquiryService::STATUS_TO_REFUND
        ]);
    }

    /**
     * Set process log created appointments
     *
     * @param ProcessLog $processLog
     */
    protected function setProcessCreatedAppointments($processLog)
    {
        $this->createdAppointments = $processLog->related_actions->filter(function($action) {
            return $action->related_type === RelatedAction::TYPE_APPOINTMENT
                && $action->action === RelatedAction::ACTION_CREATE;
        })
        ->pluck('related_id')
        ->toArray();
    }

    /**
     * Create payments for enquiry services
     *
     * @param ProcessLog $processLog
     * @param App\V1\Models\SiteEnquiry $enquiry
     */
    public function manageEnquiryServices($processLog, $enquiry)
    {
        $this->enquiry = $enquiry;
        $services = $enquiry->services;
        $this->setProcessCreatedAppointments($processLog);

        /**
         * Verify that all created appointments clinics match site enquiry clinic
         * and set to_refund on services if not
         */
        if ($this->isForeignClinic($processLog)) {
            $this->logPayment('Payments will be refund; return;');
            $this->setRefund($services);
            return;
        }

        $this->logPayment('Start managing services...');

        if ($services->count() == 0) {
            $this->logPayment('Services count 0; return;');
            return;
        }

        // Get online cashier data
        $this->onlineData = $this->getOnlineCashierData();

        if($this->enquiry->referer == 'mycabinet.info') {
            $this->serviceCashboxId = $this->onlineData['service_cashbox_pc_id'];
        } else {
            $this->serviceCashboxId = $this->onlineData['service_cashbox_id'];
        }

        //Create appointment service repository
        $this->serviceRepository = app(AppointmentServiceRepository::class);

        $this->logPayment('Cashier data: employee - ' . $this->onlineData['cashier_id'] . ', cashbox-' . $this->serviceCashboxId);

        // Create payments for not prepayments
        $ordinaryServices = $services->filter(function($service) {
            return $service->is_prepayment == false;
        });

        if ($ordinaryServices->count() != 0) {
            $this->manageOrdinary($ordinaryServices);
        }

        // Create payments for prepayments
        $prepayedServices = $services->filter(function($service) {
            return $service->is_prepayment == true && $service->service_type === Service::RELATION_TYPE;
        });

        if ($prepayedServices->count() != 0) {
            $this->managePrepayments($prepayedServices);
        }
    }

    /**
     * Create payments for appointment services
     *
     * @param ProcessLog $processLog
     * @param App\V1\Models\SiteEnquiry $enquiry
     * @param App\V1\Models\Appointment $appointment
     */
    public function manageEnquiryPayments($processLog, $enquiry, $appointment)
    {
        $this->enquiry = $enquiry;
        $services = $enquiry->services;

        $this->logPayment('Start creating payments...');

        if ($services->count() == 0) {
            $this->logPayment('Services count 0; return;');
            return;
        }

        // Get online cashier data
        $this->onlineData = $this->getOnlineCashierData();

        if($this->enquiry->referer == 'mycabinet.info') {
            $this->serviceCashboxId = $this->onlineData['service_cashbox_pc_id'];
        } else {
            $this->serviceCashboxId = $this->onlineData['service_cashbox_id'];
        }

        //Create appointment service repository
        $this->serviceRepository = app(AppointmentServiceRepository::class);

        $this->logPayment('Cashier data: employee - ' . $this->onlineData['cashier_id'] . ', cashbox-' . $this->serviceCashboxId);

        // Create payments for not prepayments
        $ordinaryServices = $services->filter(function($service) {
            return $service->is_prepayment == false;
        });

        if ($ordinaryServices->count() != 0) {
            $this->manageOrdinary($ordinaryServices);
        }

        // Create payments for prepayments
        $prepayedServices = $services->filter(function($service) {
            return $service->is_prepayment == true && $service->service_type === Service::RELATION_TYPE;
        });

        if ($prepayedServices->count() != 0) {
            $this->managePrepayments($prepayedServices);
        }
    }

    /**
     * Manage not prepayed services
     *
     * @param mixed $services
     */
    protected function manageOrdinary($services)
    {
        // Filter enquiry services with appointment_id
        $usedServices = $this->filterUsedServices($services);
        $this->logPayment('Used Services: ' . implode(',', $usedServices->pluck('service_id')->toArray()));

        // Get enquiry appointment ids
        $appointmentIds = $usedServices->pluck('appointment_id')->toArray();
        $this->logPayment('Appointment Ids: ' . implode(',', $appointmentIds));

        // Create service payments
        if ($usedServices->count() != 0 && count($appointmentIds) != 0) {
            if (!empty($this->onlineData['cashier_id']) && !empty($this->serviceCashboxId)) {
                $this->logPayment('Create payments starting...');
                $this->createEnquiryPayments($usedServices, $appointmentIds);
                $this->logPayment('Create payments finished');
            }
        }

        // Filter enquiry services without appointment_id
        $notUsedServices = $this->filterNotUsedServices($services, false);

        // Create patient deposit payments
        if ($notUsedServices->count() != 0) {
            if (!empty($this->onlineData['cashier_id']) || !empty($this->serviceCashboxId)) {
                $this->logPayment('Create deposits starting...');
                $this->createPatientDeposits($notUsedServices);
                $this->logPayment('Create deposits finished');
            }
        }
    }

    /**
     * Manage prepayments
     *
     * @param mixed
     */
    protected function managePrepayments($services)
    {
        $this->logPayment('Prepayments create starting...');
        foreach ($services as $service) {
            $prepayment = new Prepayment();
            $prepayment->amount = $service->payed_amount;
            $prepayment->patient_id = $this->enquiry->patient_id;
            $prepayment->clinic_id = $this->enquiry->clinic_id;
            $prepayment->service_id = $service->service_id;

            $payment = $prepayment->createPayment([
                'amount' => $prepayment->amount,
                'patient_id' => $prepayment->patient_id,
                'clinic_id' => $prepayment->clinic_id,
                'cashbox_id' => $this->serviceCashboxId,
                'cashier_id' => $this->onlineData['cashier_id'],
            ]);
            Log::channel($this->logChannel)->info('Payment ' . $payment->id . ' created');
            $prepayment->payment_id = $payment->id;
            $prepayment->save();
            Log::channel($this->logChannel)->info('Prepayment ' . $prepayment->id . ' created');
        }
        $this->logPayment('Prepayments create finished');
        $this->addRecordsToWaitList($services);
    }

    /**
     * Bind services to wait list records
     *
     * @param mixed $services
     */
    protected function addRecordsToWaitList($services)
    {
        $waitListServices = $this->getWaitListServices($services);

        if ($waitListServices->isNotEmpty()) {
            $this->logPayment('Add site enquiry services to wait list');
            foreach ($waitListServices as $service) {
                $record = $this->createWaitListRecord($service);
                $this->saveEnquiryServiceAttributes(collect([$service]), [
                    'wait_list_record_id' => $record->id
                ]);
            }
        }
    }

    /**
     * Get not used in appointments services
     *
     * @param mixed $services
     *
     * @return collection
     */
    protected function getWaitListServices($services)
    {
        if (empty($this->createdAppointments)) {
            return $services;
        }

        $usedServices = $this->serviceRepository->getFilteredQuery([
            'appointment_id' => $this->createdAppointments,
            'service' => $services->pluck('service_id')->toArray()
        ])
        ->pluck('service_id')->toArray();

        if (empty($usedServices)) {
            return $services;
        }

        $waitListServices = $services->filter(function($service) use ($usedServices) {
            return !in_array($service->service_id, $usedServices);
        });
        return $waitListServices;
    }

    /**
     * Update site enquiry services attributes
     *
     * @param mixed $services
     * @param array $attributes
     */
    protected function saveEnquiryServiceAttributes($services, $attributes = [])
    {
        $serviceIds = $services->pluck('id')->toArray();
        if (empty($serviceIds) || empty($attributes)) {
            return;
        }
        DB::table($this->table)
            ->whereIn('id', $serviceIds)
            ->update($attributes);
    }

    /**
     * @param mixed $service
     *
     * @return WaitListRecord
     */
    protected function createWaitListRecord($service)
    {
        $record = new WaitListRecord();
        if ($this->checkActiveTariffStatus($service)) {
            $record->status = WaitListRecord::STATUS_NEW;
        } else {
            $record->status = WaitListRecord::STATUS_PAUSE;
        }
        $record->patient_id = $this->enquiry->patient_id;
        $record->clinic_id = $this->enquiry->clinic_id;
        $record->specialization_id = $this->enquiry->specialization_id;
        $record->save();

        return $record;
    }

    /**
     * Create enquiry appointment service payments
     *
     * @param array $usedServices
     * @param array $appointmentIds
     * @param array $onlineData
     */
    protected function createEnquiryPayments($usedServices, $appointmentIds)
    {
        $enquiryServices = $this->filterServicesByType($usedServices, Service::RELATION_TYPE);
        $enquiryAnalyses =  $this->filterServicesByType($usedServices, Analysis::RELATION_TYPE);

        $servicesToPay = $this->serviceRepository->getUsedEnquiryServices(
            $enquiryServices->pluck('service_id')->all(),
            $enquiryAnalyses->pluck('service_id')->all(),
            $appointmentIds,
            $this->enquiry->clinic_id,
            $this->enquiry->patient_id
        );

        if ($enquiryAnalyses->isNotEmpty()) {
            $analysisDoctor = Employee::forAnalyses();
            $this->analysisDoctorId = $analysisDoctor ? $analysisDoctor->id : null;
        }

        if ($servicesToPay->isNotEmpty()) {
            foreach($servicesToPay as $service) {
                $cost = $this->getServiceCost($service, $enquiryServices, $enquiryAnalyses);
                if ($cost > 0) {
                    $this->createServicePayment($service, $cost);
                }
            }
        }

        // If service has appointment_id but was removed from appointment - create payments for not payed services or patient deposit
        $this->manageNotUsedServices($appointmentIds, $enquiryServices, $servicesToPay);
    }

    /**
     * Create payment for appointment service
     *
     * @param mixed $service
     * @param int|float $amount
     */
    protected function createServicePayment($service, $amount)
    {
        $doctorId = ($service->appointment->doctor_type == Employee::RELATION_TYPE) ? $service->appointment->doctor_id : $this->analysisDoctorId;
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->payed_amount = $amount;
        $payment->discount = $service->discount;
        $payment->cashbox_id = $this->serviceCashboxId;
        $payment->clinic_id = $service->clinic_id;
        $payment->service_id = $service->id;
        $payment->doctor_id = $doctorId;
        $payment->cashier_id = $this->onlineData['cashier_id'];
        $payment->patient_id = $service->patient_id;
        $payment->appointment_id = $service->appointment_id;
        $payment->payment_destination_id = $service->service->payment_destination_id;
        $payment->type = Payment::TYPE_INCOME;
        $payment->save();

        Log::channel($this->logChannel)->info('Payment ' . $payment->id . ' created');

        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$payment]);
        }
    }

    /**
     * Create payments for services in appointment that doesn't match enquiry services or patient deposit
     *
     * @param array $appointmentIds
     * @param mixed $enquiryServices
     * @param mixed $servicesToPay
     */
    protected function manageNotUsedServices($appointmentIds, $enquiryServices, $servicesToPay)
    {
        $appointmentServices = $this->getAppointmentServices($appointmentIds);
        $filteredServices = $this->getMissedServices($enquiryServices, $appointmentServices, $servicesToPay);
        $availableDeposit = $filteredServices->missed_services->sum('payed_amount');

        if ($filteredServices->appointment_services->isNotEmpty()) {
            foreach ($filteredServices->appointment_services as $service) {
                if ($availableDeposit === 0) {
                    return;
                }
                $serviceCost = (int)$service->cost;
                $amount = ($serviceCost > $availableDeposit) ? $availableDeposit : $serviceCost;
                $this->createServicePayment($service, $amount);
                $availableDeposit = $availableDeposit - $amount;
            }
        }

        if ($availableDeposit > 0) {
            $this->createPatientDeposit($availableDeposit);
        }
    }

    /**
     * Get appointment services
     *
     * @param array $appointments
     *
     * @return mixed
     */
    protected function getAppointmentServices($appointments)
    {
        return $this->serviceRepository
            ->getFilteredQuery(['appointment_id' => $appointments])
            ->with('items.service')
            ->get();
    }

    /**
     * Get enquiry services wich not in appointment when enquiry processed
     *
     * @param mixed $enquiryServices
     * @param mixed $appointmentServices
     * @param mixed $servicesToPay
     *
     * @return mixed
     */
    protected function getMissedServices($enquiryServices, $appointmentServices, $servicesToPay)
    {
        $result = collect([]);
        $result->appointment_services = collect([]);
        $result->missed_services = collect([]);

        $services = $appointmentServices->filter(function($service) {
            return $service->container_type == null;
        });

        $serviceId = array_merge(
            $services->pluck('service_id')->toArray(),
            $servicesToPay->pluck('service_id')->toArray()
        );
        $result->missed_services = $enquiryServices->filter(function($service) use ($serviceId) {
            return !in_array($service->service_id, $serviceId);
        });

        $enquiryServiceIds = $enquiryServices->pluck('service_id')->toArray();
        $result->appointment_services = $services->filter(function($service) use ($enquiryServiceIds) {
            return !in_array($service->service_id, $enquiryServiceIds);
        });
        return $result;
    }

    /**
     * Create patient deposit payments
     *
     * @param collection $services
     * @param App\V1\Models\SiteEnquiry $enquiry
     * @param array $onlineData
     */
    protected function createPatientDeposits($services)
    {
        $services->each(function($service) {
            $this->createPatientDeposit($service->payed_amount, $service->discount);
        });
    }

    /**
     * Create patient deposit
     *
     * @param int|float $amount
     * @param int|float $discount
     */
    protected function createPatientDeposit($amount, $discount = 0)
    {
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->payed_amount = $amount;
        $payment->discount = $discount;
        $payment->cashbox_id = $this->serviceCashboxId;
        $payment->clinic_id = $this->enquiry->clinic_id;
        $payment->cashier_id = $this->onlineData['cashier_id'];
        $payment->patient_id = $this->enquiry->patient_id;
        $payment->type = Payment::TYPE_INCOME;
        $payment->is_deposit = 1;
        $payment->save();

        Log::channel($this->logChannel)->info('Deposit ' . $payment->id . ' created');

        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$payment]);
        }
    }

    /**
     * Get online cashier data
     *
     * @return array
     */
    protected function getOnlineCashierData()
    {
        $onlineCashier = Employee::getOnlinePaymentCashier();

        if (!$onlineCashier) {
            Log::channel($this->logChannel)->info('Online cashier not found');
            return [];
        }

        $serviceCashboxOnline = $onlineCashier->cashboxes->first(function($cashbox) {
            return $cashbox->payment_method != null &&
                   $cashbox->payment_method->online_payment == 1 &&
                   $cashbox->payment_method->use_cash == 0;
        });

        $serviceCashboxPc = $onlineCashier->cashboxes->first(function($cashbox) {
            return $cashbox->payment_method != null &&
                   $cashbox->payment_method->pc_payment == 1 &&
                   $cashbox->payment_method->use_cash == 0;
        });

        return [
            'cashier_id' => $onlineCashier->id,
            'service_cashbox_id' => $serviceCashboxOnline ? $serviceCashboxOnline->id : null,
            'service_cashbox_pc_id' => $serviceCashboxPc ? $serviceCashboxPc->id : null,
        ];
    }

    /**
     * Filter enquiry services by appointment_id existense
     *
     * @param mixed $services
     *
     * @return mixed
     */
    protected function filterUsedServices($services)
    {
        return $services->filter(function($service) {
            return $service->appointment_id !== null;
        });
    }

    /**
     * Filter enquiry services by appointment_id not exists
     *
     * @param mixed $services
     *
     * @return mixed
     */
    protected function filterNotUsedServices($services)
    {
        return $services->filter(function($service) {
            return $service->appointment_id === null;
        });
    }

    /**
     * Get enquiry services by service_type
     *
     * @param mixed $services
     * @param string $type
     *
     * @return mixed
     */
    protected function filterServicesByType($services, $type)
    {
        return $services->filter(function($service) use ($type) {
            return $service->service_type === $type;
        });
    }

    /**
     * Get service cost by used payed enquiry services
     *
     * @param mixed $service
     * @param mixed $enquiryServices
     * @param mixed $enquiryAnalyses
     *
     * @return int
     */
    protected function getServiceCost($service, $enquiryServices, $enquiryAnalyses)
    {
        if ($service->container_type === AppointmentService::CONTAINER_ANALYSES) {
            if ($service->items->isNotEmpty()) {
                return $this->getItemsCost($service, $enquiryAnalyses);
            }
            return $service->cost;
        }

        $enquiryService = $enquiryServices->first(function($item) use ($service) {
            return $item->service_id === $service->service_id;
        });
        return $enquiryService ? $enquiryService->payed_amount : 0;
    }

    /**
     * Get service items cost sum by payed analyses cost
     *
     * @param mixed $service
     * @param mixed $enquiryAnalyses
     *
     * @return int
     */
    protected function getItemsCost($service, $enquiryAnalyses)
    {
        $analyses = $service->items->filter(function($item) {
            return $item->service_type === AnalysisResult::RELATION_TYPE;
        })->pluck('service');

        $analysesToSum = $enquiryAnalyses->filter(function($item) use ($analyses) {
            $inEnquiry = $analyses->first(function($analysis) use ($item) {
                return $analysis->analysis_id === $item->service_id;
            });
            return $inEnquiry !== null;
        });
        return $analysesToSum->sum('payed_amount');
    }

    /**
     * Add payment creation log
     *
     * @param mixed $enquiry
     * @param string $message
     */
    protected function logPayment($message)
    {
        Log::channel($this->logChannel)->info('Enquiry: ' . $this->enquiry->id . ' ' . $message);
    }

    /**
     * Check status tariff of service
     *
     * @param mixed $service
     *
     * @return boolean
    */
    protected function checkActiveTariffStatus($service)
    {
        $clinicID = $this->enquiry->clinic_id;

        return $service->service->active_base_prices->first(function($price) use ($clinicID) {
                return in_array($clinicID, $price->clinics->pluck('id')->toArray());
            }) !== null;
    }
}
