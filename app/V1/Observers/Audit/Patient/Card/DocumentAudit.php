<?php

namespace App\V1\Observers\Audit\Patient\Card;

use App\V1\Models\Patient\Card\Document;
use App\V1\Models\Specialization;
use App\V1\Observers\Audit\BaseAudit;

class DocumentAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'card_specialization_id',
    ];

    /**
     * @inherit
     */
    protected function associate($log, $model)
    {
        $log->loggable_id = $model->id;
        $log->loggable_type = Document::RELATION_TYPE;
        $log->category = $model->patient_id;
    }

    /**
     * Format card_specialization_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCardSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }

    /**
     * Format type
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNameAttribute($value)
    {
        return (string) $value;
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model);
    }

    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh);
    }

    /**
     * Get custom attributes from document model
     *
     * @param \App\V1\Models\Service $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [];

        $data['attachments'] = $model->attachments->map(function($file) {
            return sprintf('%s (%0.1fKb)', $file->name, $file->size / 1024);
        })->toArray();

        return $data;
    }
}
