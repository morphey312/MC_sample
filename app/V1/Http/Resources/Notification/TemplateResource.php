<?php

namespace App\V1\Http\Resources\Notification;

use App\V1\Http\Resources\ScopedResource;

class TemplateResource extends ScopedResource
{
    /**
     * Prepare default scopes
     *
     * @param mixed $resource
     */
    protected function prepareDefaultScopes($resource)
    {
        $resource->loadMissing([
            'channel',
            'parent',
            'clinics',
            'positions',
            'setting_clinics' => [
                'clinic'
            ],
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
            'subject' => $this->subject,
            'scenario' => $this->scenario,
            'channel_id' => $this->channel_id,
            'channel_name' => $this->get('channel.name'),
            'parent_id' => $this->parent_id,
            'parent_name' => $this->get('parent.name'),
            'header' => $this->header,
            'time' => $this->time,
            'inherit_header' => $this->inherit_header,
            'body' => $this->body,
            'inherit_body' => $this->inherit_body,
            'footer' => $this->footer,
            'specialization_id' => $this->specialization_id,
            'service_id' => $this->service_id,
            'inherit_footer' => $this->inherit_footer,
            'disabled' => $this->disabled,
            'positions' => $this->get('positions', function($positions) {
                return $positions->pluck('id');
            }),
            'clinics' => $this->get('clinics', function($clinics) {
                return $clinics->pluck('id');
            }),
            'clinic_names' => $this->get('clinics', function($clinics) {
                return $clinics->pluck('name');
            }),
            'settings_clinic_names' => $this->get('setting_clinics', function($clinics) {
                return $clinics->map(function($clinic) {
                    return $clinic->clinic->name;
                });
            }),
            'settings_clinic' => $this->get('setting_clinics', function($clinics) {
                return $clinics->map(function($clinic) {
                    return $clinic->clinic->id;
                });
            }),
            'voip_queue' => $this->get('voip_queue', function($queues) {
                return $queues->map(function($queue) {
                    return [
                        'id' => $queue->id,
                        'queue' => $queue->queue,
                        'notification_template_id' => $queue->notification_template_id
                    ];
                });
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
