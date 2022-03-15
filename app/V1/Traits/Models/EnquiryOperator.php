<?php

namespace App\V1\Traits\Models;

use App\V1\Contracts\Repositories\EmployeeRepository;

trait EnquiryOperator
{
    /**
     * Assign this enquiry to operator
     *
     * Choose an operator whit minimal amount of enquiries for last 24 hrs
     * among free (first priority), available (second priority)
     * or any (third priority) operators from corresponding clinic
     */
    public function assignToOperator()
    {
        $employees = app(EmployeeRepository::class);
        $this->operator = $employees->findOperatorForEnquiry($this->clinic_id, $this->category_group);
    }
}