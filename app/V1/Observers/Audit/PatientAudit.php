<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Patient;
use App\V1\Models\Patient\InformationSource;
use App\V1\Models\Clinic;
use App\V1\Models\DiscountCardType;
use App\V1\Facades\Handbook as HandbookService;
use App\V1\Models\Handbook;
use App\V1\Models\LegalEntity;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PatientAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'firstname',
        'lastname',
        'middlename',
        'passport_no',
        'gender',
        'status',
        'med_insurance',
        'location',
        'house_number',
        'apartment_number',
        'birthday',
        'comment',
        'mailing',
        'mailing_analysis',
        'black_mark',
        'black_mark_reason',
        'black_mark_comment',
        'is_skk',
        'is_attention',
        'skk_reason',
        'skk_comment',
        'source_id',
        'is_confirmed',
        'has_registration',
        'legal_entity_id',
    ];

    /**
     * Format birthday
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatBirthdayAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format mailing
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatMailingAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format mailing analysis
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatMailingAnalysisAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format black mark
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatBlackMarkAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is skk
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsSkkAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is attention
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsAttentionAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format source
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatSourceIdAttribute($value)
    {
        return $this->fetchAttribute(InformationSource::class, $value, 'name');
    }

    /**
     * Format is_confirmed
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsConfirmedAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format has_registration
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatHasRegistrationAttribute($value)
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
            + $this->getCustomRelations($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model)
            + $this->getCustomRelationsUpdates($model);
    }

    /**
     * Get custom attributes from patient model
     *
     * @param \App\V1\Models\Patient $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [
            'clinics' => $model->clinics->pluck('name')->all(),
        ];

        if ($primary = $model->primary_phone) {
            $data['primary_phone_number'] = $primary->value;
            $data['primary_phone_clinic'] = object_get($primary, 'clinic.name');
            $data['primary_phone_comment'] = $primary->comment;
        }

        if ($secondary = $model->additional_phone) {
            $data['secondary_phone_number'] = $secondary->value;
            $data['secondary_phone_clinic'] = object_get($secondary, 'clinic.name');
            $data['secondary_phone_comment'] = $secondary->comment;
        }

        if ($email = $model->email) {
            $data['email'] = $email->value;
        }

        return $data;
    }

    /**
     * Get custom relations from patient model
     *
     * @param \App\V1\Models\Patient $model
     *
     * @return array
     */
    protected function getCustomRelations($model)
    {
        return [
            'issued_discount_cards' => $this->getIssuedDiscountCards($model),
            'relatives' => $this->getRelatives($model),
        ];
    }

    /**
     * Get already issued discount cards
     *
     * @param \App\V1\Models\Patient $model
     *
     * @return array
     */
    protected function getIssuedDiscountCards($model)
    {
        return $model->issued_discount_cards->map(function($card) use ($model) {
            $ownerData = null;
            $cardOwner = $card->card_owner->first();

            if ($cardOwner->id !== $model->id) {
                $ownerData = ', Владелец - ' . $cardOwner->full_name;
            }
            return sprintf('%s - %s %s %s',
                $card->clinic->name,
                $card->discount_card_type->name,
                $card->number,
                ($ownerData ? $ownerData : '')
            );
        })->all();
    }

    /**
     * Get already existing relatives
     *
     * @param \App\V1\Models\Patient $model
     *
     * @return array
     */
    protected function getRelatives($model)
    {
        return $model->relatives->map(function($relation) {
            return sprintf('%s - %s%s',
                $relation->full_name,
                HandbookService::get(Handbook::CATEGORY_PATIENT_RELATIVE . '.' . $relation->pivot->relation),
                $this->encodeCabinetValue(
                    $relation->pivot->show_in_cabinet,
                    $relation->pivot->show_in_cabinet_after_14,
                    $relation->pivot->cabinet_deny_reason
                )
            );
        })->all();
    }

    /**
     * Get custom relations updates on patient model
     *
     * @param \App\V1\Models\Patient $model
     *
     * @return array
     */
    protected function getCustomRelationsUpdates($model)
    {
        return [
            'issued_discount_cards' => $model->discountCardsToSave === null
                ? $this->getIssuedDiscountCards($model)
                : $this->getIssuedDiscountCardsUpdates($model->discountCardsToSave, $model),
            'relatives' => $model->relativesToSave === null
                ? $this->getRelatives($model)
                : $this->getRelativesUpdates($model->relativesToSave),
        ];
    }

    /**
     * Get issued discount cards updates
     *
     * @param array $updates
     *
     * @return array
     */
    protected function getIssuedDiscountCardsUpdates($updates, $model)
    {
        return collect($updates)->map(function($card) use ($model) {
            $ownerData = null;
            $cardOwner = Arr::first($card['patients'], function($patient) {
                return $patient['is_owner'] == 1;
            });

            if ($cardOwner && $cardOwner['patient_id'] !== $model->id) {
                $ownerString = ', Владелец - ';
                if (isset($cardOwner['name'])) {
                    $ownerData = $ownerString . $cardOwner['name'];
                } elseif ($model->id === null) {
                    $ownerData = $ownerString . $model->full_name;
                }
            }

            return sprintf('%s - %s %s %s',
                $this->fetchAttribute(Clinic::class, $card['clinic_id'], 'name'),
                $this->fetchAttribute(DiscountCardType::class, $card['discount_card_type_id'], 'name'),
                $card['number'],
                ($ownerData ? $ownerData : '')
            );
        })->all();
    }

    /**
     * Get relatives updates
     *
     * @param array $updates
     *
     * @return array
     */
    protected function getRelativesUpdates($updates)
    {
        return collect($updates)->map(function($relation) {
            return sprintf('%s - %s%s',
                $this->fetchAttribute(Patient::class, $relation['id'], 'full_name'),
                HandbookService::get(Handbook::CATEGORY_PATIENT_RELATIVE . '.' . $relation['relation']),
                $this->encodeCabinetValue(
                    $relation['show_in_cabinet'],
                    $relation['show_in_cabinet_after_14'],
                    $relation['cabinet_deny_reason']
                )
            );
        })->all();
    }

    /**
     * Get text value for cabinet availability
     *
     * @param bool $show_in_cabinet
     * @param bool $show_in_cabinet_after_14
     * @param string $cabinet_deny_reason
     *
     * @return string
     */
    protected function encodeCabinetValue($show_in_cabinet, $show_in_cabinet_after_14, $cabinet_deny_reason)
    {
        if ($show_in_cabinet_after_14) {
            return ' (+ЛК после 14)';
        }
        if ($show_in_cabinet) {
            return ' (+ЛК до 14)';
        }
        if ($cabinet_deny_reason) {
            return ' (без ЛК: ' . $cabinet_deny_reason . ')';
        }
        return '';
    }

     /**
     * Format legal entity
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatLegalEntityIdAttribute($value)
    {
        return $this->fetchAttribute(LegalEntity::class, $value, 'name');
    }
}
