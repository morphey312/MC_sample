<?php

namespace App\V1\Console\Commands\Ehealth;

use Illuminate\Console\Command;
use App\V1\Contracts\Services\EhealthService;
use App\V1\Contracts\Repositories\MspRepository;
use App\V1\Contracts\Repositories\EmployeeRepository;
use App\V1\Contracts\Repositories\Msp\ContractRepository;
use Illuminate\Support\Arr;
use App\V1\Models\Msp\Contract;
use Exception;
use Illuminate\Support\Facades\Log;

class CheckStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ehealth:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check MSP status in ehealth';

    /**
     * Execute the console command.
     *
     * @param EhealthService $ehealth
     * @param MspRepository $msp
     * @param EmployeeRepository $employees
     * @param ContractRepository $contracts
     */
    public function handle(
        EhealthService $ehealth, 
        MspRepository $msp, 
        EmployeeRepository $employees,
        ContractRepository $contracts
    ) {
        foreach ($msp->getPending() as $model) {
            $this->checkMspStatus($model, $ehealth, $msp);
        }

        foreach ($employees->getPending() as $model) {
            $this->checkEmployeeStatus($model, $ehealth, $employees);
        }

        foreach ($contracts->getPending() as $model) {
            $this->checkContractStatus($model, $ehealth, $contracts);
        }
    }

    /**
     * Check status of the MSP
     * 
     * @param \App\V1\Models\Msp $model
     * @param EhealthService $ehealth
     * @param MspRepository $msp
     */
    protected function checkMspStatus($model, $ehealth, $msp)
    {
        try {
            $response = $ehealth->getLegalEntity($model->ehealth_id);

            $status = Arr::get($response, 'data.status', null);
            $nhsReviewed = Arr::get($response, 'data.nhs_reviewed', null);
            $nhsVerified = Arr::get($response, 'data.nhs_verified', null);
            $nhsComment = Arr::get($response, 'data.nhs_comment', null);

            if ($status !== null) {
                $model->ehealth_status = $status;
            }
            if ($nhsReviewed !== null) {
                $model->nhs_reviewed = $nhsReviewed;
            }
            if ($nhsVerified !== null) {
                $model->nhs_verified = $nhsVerified;
            }
            if ($nhsComment !== null) {
                $model->nhs_comment = $nhsComment;
            }

            $msp->persist($model);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * Check status of the MSP
     * 
     * @param \App\V1\Models\Employee $model
     * @param EhealthService $ehealth
     * @param EmployeeRepository $employees
     */
    protected function checkEmployeeStatus($model, $ehealth, $employees)
    {
        try {
            $response = $ehealth->getEmployeeRequest($model->ehealth_request_id);

            $status = Arr::get($response, 'data.status', null);
            $id = Arr::get($response, 'data.employee_id', null);

            if ($status === $model->ehealth_request_status) {
                return;
            }

            if ($status !== null) {
                $model->ehealth_request_status = $status;
            }
            if ($id !== null) {
                $model->ehealth_id = $id;
            }

            $employees->persist($model);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * Check status of the MSP
     * 
     * @param \App\V1\Models\Employee $model
     * @param EhealthService $ehealth
     * @param ContractRepository $contracts
     */
    protected function checkContractStatus($model, $ehealth, $contracts)
    {
        try {
            $ehealth_user = $model->ehealth_user;
            
            if ($ehealth_user === null) {
                return;
            }

            $token = $ehealth->getToken($ehealth_user);

            if ($token === null) {
                return;
            }

            $response = $ehealth->getContractRequest($model->type, $model->ehealth_request_id, $token);

            $statusReason = Arr::get($response, 'data.status_reason', null);
            $status = Arr::get($response, 'data.status', null);

            if ($status === $model->ehealth_status) {
                return;
            }

            if ($status !== null) {
                $model->ehealth_status = $status;
            }
            if ($statusReason !== null) {
                $model->ehealth_status_reason = $statusReason;
            }

            $contracts->persist($model);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
