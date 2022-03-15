<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Models\Patient\Upload;
use App\V1\Observers\Audit\BaseAudit;

class UploadAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'type',
        'file_data',
    ];

    /**
     * @inherit
     */
    protected function associate($log, $model)
    {
        $log->loggable_id = $model->id;
        $log->loggable_type = Upload::RELATION_TYPE;
        $log->category = $model->patient->id;
    }

    /**
     * Format type
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatTypeAttribute($value)
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
     * Get custom attributes from patient model
     *
     * @param \App\V1\Models\Clinic $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [
            'file' => $this->getFileInfo($model->file),
        ];

        return $data;
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
