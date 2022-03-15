<?php

namespace App\V1\Http\Controllers;

use Illuminate\Http\Request;
use App\V1\Contracts\Repositories\PersonalTaskRepository;
use App\V1\Contracts\Repositories\SiteEnquiryRepository;
use App\V1\Contracts\Repositories\WaitListRecordRepository;
use App\V1\Models\PersonalTask;
use App\V1\Models\SiteEnquiry;
use App\V1\Models\WaitListRecord;

class VoipController extends ApiController
{
    /**
     * Get counters values
     * 
     * @param Request $request
     * @param PersonalTaskRepository $tasks
     * @param SiteEnquiryRepository $enquiries
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function counters(Request $request, 
        PersonalTaskRepository $tasks, 
        SiteEnquiryRepository $enquiries,
        WaitListRecordRepository $waitListRecords) {
        $employee = $request->user()->getEmployeeModel();
        if ($employee === null) {
            return $this->respondError('User is not an employee');
        }
        
        $tasksCount = $tasks->count($tasks->filter([
            'operator' => $employee->id,
            'status' => PersonalTask::STATUS_NEW,
        ]));
        
        $enquiriesCount = $enquiries->count($enquiries->filter([
            'operator' => $employee->id,
            'status' => SiteEnquiry::STATUS_NEW,
        ]));

        $waitListRecordsCount = $waitListRecords->count($waitListRecords->filter([
            'operator' => $employee->id,
            'status' => WaitListRecord::STATUS_NEW,
        ]));
        
        return $this->respondSuccess([
            'tasks' => $tasksCount,
            'enquiries' => $enquiriesCount,
            'wait_list_records' => $waitListRecordsCount,
        ]);
    }
}