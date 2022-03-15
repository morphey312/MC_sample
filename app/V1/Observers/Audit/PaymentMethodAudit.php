<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Clinic;
use App\V1\Repositories\ClinicRepository;
use Illuminate\Support\Arr;

class PaymentMethodAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'use_cash',
        'is_enabled',
        'online_payment',
        'pc_payment',
        'change_payment_date',
    ];

    /**
     * Format is_online
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatOnlinePaymentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_enabled
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsEnabledAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format use_cash
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatUseCashAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format use_cash
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatPcPaymentAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + $this->getClinics($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model)
            + $this->getClinics($model);
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
        $fiscalClinicPayments = [];

        if ($model->clinicsToSave !== null) {
            $clinicsIds = Arr::pluck($model->clinicsToSave, 'clinic_id');
            $fiscalsMapping = Arr::pluck($model->clinicsToSave, 'is_fiscal', 'clinic_id');
            $clinics = app(ClinicRepository::class)->find($clinicsIds);

            foreach ($clinics as $clinic) {
                foreach ($fiscalsMapping as $key => $fiscal) {
                    if($key === $clinic->id) {
                        $fiscalClinicPayments[] = $clinic->name . '|' . ($fiscal ? 1 : 0);
                    }
                }
            }
        } else {
            $clinicsIds = $model->clinics->pluck('id')->toArray();
            $clinics = app(ClinicRepository::class)->getWithPaymentMethod($model->id, $clinicsIds);

            foreach ($clinics as $clinic) {
                $fiscalClinicPayments[] = $clinic->name
                             . '|' .
                             $clinic->payment_methods->first()->pivot->is_fiscal;
            }
        }

        $data['fiscal'] = $fiscalClinicPayments;

        return $data;
    }

    /**
     * Get custom attributes from user model
     *
     * @param \App\V1\Models\Service $model
     *
     * @return array
     */
    protected function getClinics($model)
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
}
