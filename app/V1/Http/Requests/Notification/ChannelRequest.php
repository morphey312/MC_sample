<?php

namespace App\V1\Http\Requests\Notification;

use App\V1\Http\Requests\BaseRequest;
use App\V1\Models\Notification\Channel;
use App\V1\Facades\Handbook as HandbookService;
use App\V1\Models\Handbook;

class ChannelRequest extends BaseRequest
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
            'name' => 'required|string|max:255|unique_accessible:Notification\ChannelRepository,name,' . $id,
            'type' => 'required|in:' . implode(',', HandbookService::keys(Handbook::CATEGORY_NOTIFICATION_CHANNEL_TYPE)),
            'account_name' => 'nullable|string|max:255',
            'account_password' => 'nullable|string|max:255',
            'settings' => 'array',
        ];
    }
    
    /**
     * Get safe data from request
     *
     * @return  array
     */
    public function safe()
    {
        return $this->only((new Channel)->getFillable());
    }
}
