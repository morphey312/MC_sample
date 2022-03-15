<?php

namespace App\V1\Observers\Call;

use App\V1\Models\Call\ProcessLog;
use App\V1\Contracts\Repositories\Call\ProcessLogRepository;
use App\V1\Contracts\Repositories\Call\CallLogRepository;
use App\V1\Contracts\Repositories\PatientRepository;
use App\V1\Contracts\Repositories\EmployeeRepository;
use App\V1\Contracts\Repositories\SiteEnquiryRepository;
use App\V1\Models\Call\CallLog;
use App\V1\Models\SiteEnquiry;
use App\V1\Models\Patient;
use App\V1\Models\Employee;
use App\V1\Models\WaitListRecord;
use App\V1\Traits\PhoneNumber;
use OnlinePaymentService;

class ProcessLogObserver
{
    use PhoneNumber;

    /**
     * @var ProcessLogRepository
     */
    protected $repository;

    public function __construct(ProcessLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Listen to creating event
     *
     * @param ProcessLog $model
     */
    public function creating(ProcessLog $model)
    {
        $this->bindContact($model);
    }

    /**
     * Listen to created event
     *
     * @param ProcessLog $model
     */
    public function created(ProcessLog $model)
    {
        $this->autocompleteProcess($model);
        $this->updateEnquiryStatus($model);
    }

    /**
     * Listen to updating event
     *
     * @param ProcessLog $model
     */
    public function updating(ProcessLog $model)
    {
        if ($model->getOriginal('status') === ProcessLog::STATUS_NONPROCESSED && $model->status !== ProcessLog::STATUS_NONPROCESSED) {
            $this->updateEnquiryStatus($model);
        }
    }

    /**
     * Update enquiry status
     *
     * @param ProcessLog $model
     */
    protected function updateEnquiryStatus(ProcessLog $model)
    {
        if ($model->hasEnquiry()) {
            $enquiry = $model->enquiry;

            if ($model->status === ProcessLog::STATUS_PROCESSED) {
                $enquiry->status = SiteEnquiry::STATUS_PROCESSED;
            } elseif ($model->status === ProcessLog::STATUS_NONPROCESSED) {
                $enquiry->status = SiteEnquiry::STATUS_NOT_PROCESSED;
            } else {
                $enquiry->status = SiteEnquiry::STATUS_IMPROCESSIBLE;
            }

            if ($enquiry->category === SiteEnquiry::CATEGORY_PAYMENT) {
                if ($enquiry->appointment !== null) {
                    $enquiry->patient_id = $enquiry->appointment->patient_id;
                }

                $enquiry->save();

                if ($this->shouldMakePayments($enquiry)) {
                    OnlinePaymentService::manageEnquiryPayments($model, $enquiry, $enquiry->appointment);
                }
            } else {
                if ($model->contact_type === Patient::RELATION_TYPE && $enquiry->patient_id === null) {
                    $enquiry->patient_id = $model->contact_id;
                }

                $enquiry->save();

                if ($this->shouldMakePayments($enquiry)) {
                    OnlinePaymentService::manageEnquiryServices($model, $enquiry);
                }
            }
        } elseif ($model->hasWaitListRecord()) {
            $record = $model->wait_list_record;

            if ($model->status === ProcessLog::STATUS_PROCESSED) {
                $record->status = WaitListRecord::STATUS_PROCESSED;
            } else {
                $record->status = WaitListRecord::STATUS_NOT_PROCESSED;
            }

            $record->save();
        }
    }

    /**
     * Bind patient/employee to related context
     *
     * @param ProcessLog $model
     */
    protected function bindContact($model)
    {
        $context = $this->getContext($model);

        if ($context instanceof CallLog) {
            $model->phone_number = $context->phone_number;
            if ($model->contact_type === Patient::RELATION_TYPE) {
                $patient = $this->findPatientById($model->contact_id);
                $context->versa = $patient;
                $context->patient = $patient;
                $this->saveCall($context);
            } elseif ($model->contact_type === Employee::RELATION_TYPE) {
                if ($model->is_incoming_call == 1 && $context->type === CallLog::TYPE_INCOMING) {
                    // Operator that accepts the incoming call will define a caller
                    $employee = $this->findEmployeeById($model->contact_id);
                    $context->caller = $employee;
                    $context->patient_id = null;
                    $this->saveCall($context);
                } elseif ($model->is_incoming_call != 1 && $context->type === CallLog::TYPE_OUTGOING) {
                    // Operator that makes the outgoung call will define a callee
                    $employee = $this->findEmployeeById($model->contact_id);
                    $context->callee = $employee;
                    $context->patient_id = null;
                    $this->saveCall($context);
                }
            }
        } elseif ($context instanceof SiteEnquiry) {
            $model->phone_number = $context->phone_number;
            if ($model->contact_type === Patient::RELATION_TYPE) {
                $patient = $this->findPatientById($model->contact_id);
                $context->patient = $patient;
                $this->saveEnquiry($context);
            }
        }
    }

    /**
     * Get patient by id
     *
     * @param int $id
     *
     * @return Patient
     */
    protected function findPatientById($id)
    {
        $patients = app()->make(PatientRepository::class);
        return $patients->find($id, false);
    }

    /**
     * Get employee by id
     *
     * @param int $id
     *
     * @return Employee
     */
    protected function findEmployeeById($id)
    {
        $employees = app()->make(EmployeeRepository::class);
        return $employees->find($id, false);
    }

    /**
     * Save call
     *
     * @param CallLog $call
     */
    protected function saveCall($call)
    {
        $calls = app()->make(CallLogRepository::class);
        $calls->persist($call);
    }

    /**
     * Save enquiry
     *
     * @param SiteEnquiry $enquiry
     */
    protected function saveEnquiry($enquiry)
    {
        $enquiries = app()->make(SiteEnquiryRepository::class);
        $enquiries->persist($enquiry);
    }

    /**
     * Autocomplete process logs
     *
     * @param ProcessLog $model
     */
    protected function autocompleteProcess($model)
    {
        if ($model->status === ProcessLog::STATUS_NONPROCESSED) {
            return;
        }

        if ($model->status === ProcessLog::STATUS_IMPROCESSIBLE &&
            $model->contact_type === Employee::RELATION_TYPE) {
            return;
        }

        if (!$model->phone_number && !$model->contact_id) {
            return;
        }

        if ($model->status === ProcessLog::STATUS_PROCESSED) {
            foreach ($this->getNonProcessedLogs($model) as $log) {
                $log->status = ProcessLog::STATUS_PROCESSED;
                $log->is_patient = $model->is_patient;
                $log->is_first_visit = $model->is_first_visit;
                $log->source = $model->source;
                $log->clinic_id = $model->clinic_id;
                $log->auto_process_id = $model->id;
                if ($log->contact_type === null) {
                    $log->contact_type = $model->contact_type;
                    $log->contact_id = $model->contact_id;
                }
                $this->repository->persist($log);
            }

            if ($model->contact_type === Patient::RELATION_TYPE) {
                WaitListRecord::autoprocessRecords($model);
            }
        } elseif ($model->status === ProcessLog::STATUS_IMPROCESSIBLE) {
            foreach ($this->getNonProcessedLogs($model, true) as $log) {
                $log->status = ProcessLog::STATUS_IMPROCESSIBLE;
                $log->unprocessibility_reason = $model->unprocessibility_reason;
                $log->unprocessibility_reason_comment = $model->unprocessibility_reason_comment;
                $log->auto_process_id = $model->id;
                $this->repository->persist($log);
            }
        }
    }

    /**
     * Get context of this processing
     *
     * @param ProcessLog $model
     *
     * @return mixed
     */
    protected function getContext($model)
    {
        if ($model->call_id) {
            return $model->call;
        }

        if ($model->enquiry_id) {
            return $model->enquiry;
        }

        if ($model->wait_list_record_id) {
            return $model->wait_list_record;
        }

        return null;
    }

    /**
     * Get non-processed logs
     *
     * @param ProcessLog $model
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getNonProcessedLogs($model, $excludeSiteEnquiry = false)
    {
        return $this->repository->getNonProcessedLogs(
            $model->phone_number,
            $model->contact_id,
            $model->contact_type,
            24,
            $excludeSiteEnquiry
        );
    }

    /**
     * Check if payments should be created for this enquiry 
     * 
     * @param SiteEnquiry $enquiry
     * 
     * @return bool
     */
    protected function shouldMakePayments($enquiry)
    {
        return $enquiry->patient_id != null
            && $enquiry->status === SiteEnquiry::STATUS_PROCESSED 
            && $enquiry->payment_status === SiteEnquiry::PAYMENT_STATUS_PAYED;
    }
}
