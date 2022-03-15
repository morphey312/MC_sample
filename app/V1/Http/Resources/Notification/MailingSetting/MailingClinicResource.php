<?php

namespace App\V1\Http\Resources\Notification\MailingSetting;

use App\V1\Http\Resources\ScopedResource;

class MailingClinicResource extends ScopedResource
{
    /**
     * Prepare default scopes
     *
     * @param mixed $resource
     */
    protected function prepareDefaultScopes($resource)
    {
        $resource->loadMissing([
            'specializations',
            'clinic',
        ]);
    }

    /**
     * Transform the resource into an array
     *
     * @param \Illuminate\Http\Request $request
     * @return  array
     */
    public function toArray($request)
    {
        $specializations = $this->get('specializations', function ($specializations) {
            return $specializations;
        });

        return [
            'id' => $this->id,
            'clinic' => ['name' => $this->get('clinic.name'),
                ],
            'clinic_id' => $this->clinic_id,
            'notification_mailing_template_id' => $this->notification_mailing_template_id,
            'active' => $this->active,
            'specializations' => $specializations->map(function ($specialization) {
                return [
                    'specialization_id' => $specialization->specialization_id,
                    'custom_name' => $specialization->custom_name
                ];
            }),
            'specialization_names' => $specializations->map(function ($specialization) {
                return $specialization->specialization->name;
            }),
        ];
    }
}
