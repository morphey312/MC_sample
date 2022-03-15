<?php

namespace App\V1\Http\Requests\Notification;

use App\V1\Http\Requests\BaseRequest;
use App\V1\Models\Notification\Template;
use App\V1\Facades\Handbook as HandbookService;
use App\V1\Models\Handbook;

class TemplateRequest extends BaseRequest
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
            'name' => 'required|string|max:255|unique_accessible:Notification\TemplateRepository,name,' . $id,
            'subject' => 'nullable|string|max:255',
            'scenario' => 'required|in:' . implode(',', HandbookService::keys(Handbook::CATEGORY_NOTIFICATION_SCENARIO)),
            'channel_id' => 'required|integer|accessible:Notification\ChannelRepository',
            'parent_id' => 'nullable|integer|accessible:Notification\TemplateRepository',
            'header' => 'nullable|string|max:65535',
            'inherit_header' => 'required|boolean',
            'body' => 'nullable|string|max:65535',
            'time' => 'nullable|date_format:H:i:s',
            'inherit_body' => 'required|boolean',
            'footer' => 'nullable|string|max:65535',
            'inherit_footer' => 'required|boolean',
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
        return $this->only((new Template)->getFillable());
    }
}
