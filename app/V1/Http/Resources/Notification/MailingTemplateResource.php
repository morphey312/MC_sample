<?php

namespace App\V1\Http\Resources\Notification;

use App\V1\Http\Resources\ScopedResource;

class MailingTemplateResource extends ScopedResource
{
    /**
     * Prepare default scopes
     *
     * @param mixed $resource
     */
    protected function prepareDefaultScopes($resource)
    {
        $resource->loadMissing([
            'provider',
            'clinics',
        ]);
    }

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
            'scenario' => $this->scenario,
            'channel_id' => $this->channel_id,
            'channel_name' => $this->get('provider.name'),
            'mailing_time' => $this->mailing_time,
            'schedule_mailing' => $this->schedule_mailing,
            'disabled' => $this->disabled,
            'clinics' => $this->get('clinics', function($clinics) {
                return $clinics->pluck('id');
            }),
            'clinic_names' => $this->get('clinics', function($clinics) {
                return $clinics->pluck('name');
            }),
            'statuses' => $this->get('statuses', function($clinics) {
                return $clinics->pluck('id');
            }),
            'status_names' => $this->get('statuses', function($clinics) {
                return $clinics->pluck('name');
            }),
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
