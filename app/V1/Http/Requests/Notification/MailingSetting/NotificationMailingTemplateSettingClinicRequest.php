<?php

namespace App\V1\Http\Requests\Notification\MailingSetting;

use App\V1\Http\Requests\BaseRequest;
use App\V1\Models\Notification\MailingSetting\MailingClinic;

class NotificationMailingTemplateSettingClinicRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return  array
     */
    public function rules()
    {
        return [
            'clinic_id' => 'required|integer|accessible:ClinicRepository',
            'notification_mailing_template_id' => 'required|integer|accessible:Notification\MailingTemplateRepository',
            'active' => 'required|boolean',
        ];
    }

    /**
     * Get safe data from request
     *
     * @return  array
     */
    public function safe()
    {
        return $this->only((new MailingClinic)->getFillable());
    }
}
