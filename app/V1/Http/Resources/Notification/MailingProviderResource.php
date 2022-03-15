<?php

namespace App\V1\Http\Resources\Notification;

use App\V1\Http\Resources\ScopedResource;

class MailingProviderResource extends ScopedResource
{
    /**
     * Transform the resource into an array
     *
     * @param    \Illuminate\Http\Request  $request
     * @return  array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'account_name' => $this->account_name,
            'account_password' => $this->account_password,
            'settings' => $this->settings,
        ];
    }

    /**
     * @inheritdoc
     */
    public function toOption($data)
    {
        return [
            'id' => $data->id,
            'value' => $data->name,
        ];
    }
}
