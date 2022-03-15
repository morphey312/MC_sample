<?php

namespace App\V1\Http\Requests\Notification;

use App\V1\Http\Requests\BaseRequest;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Notification\Template;
use App\V1\Facades\Handbook as HandbookService;
use App\V1\Models\Handbook;

class MailingTemplateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return  array
     */
    public function rules()
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255|unique_accessible:Notification\MailingTemplateRepository,name,' . $id,
            'scenario' => 'required|in:' . implode(',', HandbookService::keys(Handbook::CATEGORY_NOTIFICATION_MAILING_SCENARIO)),
            'channel_id' => 'required|integer|accessible:Notification\MailingProviderRepository',
            'disabled' => 'required|boolean',
            'mailing_time' => 'nullable|date_format:H:i:s',
            'schedule_mailing' => 'required|boolean',
            'disabled' => 'required|boolean',
            'positions' => 'array',
            'positions.*' => 'integer|accessible:Employee\PositionRepository',
            'clinics' => 'array',
            'clinics.*' => 'integer|accessible:ClinicRepository',
        ];
    }

    /**
     * Get safe data from request
     *
     * @return  array
     */
    public function safe()
    {
        return $this->only((new MailingTemplate)->getFillable());
    }
}
