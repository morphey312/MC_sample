<?php

namespace App\V1\Observers\Audit\Clinic;

use App\V1\Models\Clinic\Group;
use App\V1\Models\Clinic\MoneyReciever;
use App\V1\Models\Country;
use App\V1\Models\Msp;
use App\V1\Observers\Audit\BaseAudit;

class ClinicAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'additional_contact_phone',
        'contact_phone',
        'analysis_check_url',
        'authority_name',
        'city',
        'country_id',
        'currency',
        'email',
        'group_id',
        'name',
        'kind',
        'lat',
        'lng',
        'map_link',
        'money_reciever_id',
        'msp_id',
        'not_round_cost',
        'official_additional',
        'official_name',
        'short_name',
        'signer',
        'signer_position',
        'status',
        'voip_queue',
        'medicine_stores',
        'working_hours',
        'need_apply_city'
    ];

    /**
     * Format country_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCountryIdAttribute($value)
    {
        return $this->fetchAttribute(Country::class, $value, 'name');
    }

    /**
     * Format lat
     *
     * @param mixed $value
     *
     * @return
     */
    protected function formatLatAttribute($value)
    {
        return number_format($value, 5);
    }

    /**
     * Format lng
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatLngAttribute($value)
    {
        return number_format($value, 5);
    }

    /**
     * Format not_round_cost
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNotRoundCostAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format money_reciever_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatMoneyRecieverIdAttribute($value)
    {
        return $this->fetchAttribute(MoneyReciever::class, $value, 'name');
    }

    /**
     * Format msp_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatMspIdAttribute($value)
    {
        return $this->fetchAttribute(Msp::class, $value, 'msp_id');
    }

    /**
     * Format group_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatGroupIdAttribute($value)
    {
        return $this->fetchAttribute(Group::class, $value, 'name');
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomRelations($model)
            + $this->getCustomAttributes($model);
    }

    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomRelations($fresh)
            + $this->getCustomAttributes($fresh);
    }


    /**
     * Get custom relations from clinic model
     *
     * @param \App\V1\Models\Clinic $model
     *
     * @return array
     */
    protected function getCustomRelations($model)
    {
        return [
            'medicine_stores' => $this->getMedicineStores($model),
        ];
    }

    /**
     * Get custom attributes from patient model
     *
     * @param \App\V1\Models\Clinic $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [
            'country' => $model->address->country,
            'address' => $model->address->address,
            'image' => $this->getFileInfo($model->image),
            'questionnaire_blank' => $this->getFileInfo($model->questionnaire),
        ];


        foreach ($model->blanks as $blank) {
            $data[$blank->type] = $this->getFileInfo($blank->attachments->first());
        }

        return $data;
    }

    /**
     * Get used medicine store names
     *
     * @param \App\V1\Models\Clinic $model
     *
     * @return array
     */
    protected function getMedicineStores($model)
    {
        return $model->medicine_stores->map(function ($medicine) {
            return $medicine->description;
        });
    }

    /**
     * Get file information
     *
     * @param App\V1\Models\FileAttachment $model
     *
     * @return string
     */
    protected function getFileInfo($model)
    {
        if (!is_null($model)) {
            return sprintf('%s (%0.1fKb)', $model->name, $model->size / 1024);
        }

        return null;
    }
}
