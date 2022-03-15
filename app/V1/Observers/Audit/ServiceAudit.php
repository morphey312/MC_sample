<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Specialization;
use App\V1\Models\Clinic;
use App\V1\Models\Service\PaymentDestination;
use Illuminate\Support\Arr;

class ServiceAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'name_ua',
        'specialization_id',
        'disabled',
        'is_for_discount_card',
        'is_base',
        'payment_destination_id',
        'is_online',
        'site_service_type',
        'for_foreigners'
    ];

    /**
     * Format specialization_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }

    /**
     * Format disabled
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatDisabledAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_for_discount_card
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsForDiscountCardAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_base
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsBaseAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format payment_destination_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatPaymentDestinationIdAttribute($value)
    {
        return $this->fetchAttribute(PaymentDestination::class, $value, 'name');
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model);
    }

    /**
     * Get custom attributes from user model
     *
     * @param \App\V1\Models\Service $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [];

        if ($model->clinicsToSave !== null) {
            $clinicIds = Arr::pluck($model->clinicsToSave, 'clinic_id');
            $data['clinics'] = $this->fetchAttribute(Clinic::class, $clinicIds, 'name');
        } else {
            $data['clinics'] = $model->clinics->pluck('name')->all();
        }

        return $data;
    }

    /**
     * Format is_online
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsOnlineAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format for_foreigners
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatForForeignersAttribute($value)
    {
        return (bool) $value;
    }
}
