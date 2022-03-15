<?php

namespace App\V1\Contracts\Services;

use App\V1\Models\Call\ProcessLog;
use App\V1\Models\SiteEnquiry;

interface OnlinePaymentService
{
    /**
     * Create payments for enquiry services
     *
     * @param ProcessLog $model
     * @param SiteEnquiry $enquiry
     */
    public function manageEnquiryServices($model, $enquiry);
}
