<?php

namespace App\V1\Observers\Audit\Analysis;

use App\V1\Models\ActionLog;
use App\V1\Models\InsuranceCompany\Act;
use App\V1\Observers\Audit\BaseAudit;
use Illuminate\Support\Facades\Auth;

class ResultAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'quantity',
	    'cost',
	    'discount',
	    'status',
	    'date_expected_pass',
	    'date_pass',
	    'date_expected_ready',
	    'date_ready',
        'date_sent_email',
	    'delivery_status',
	    'warranter',
	    'by_policy',
	    'franchise',
    ];

    /**
     * Listen to printed event
     *
     * @param Act $model
     */
    public function printed($model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model);
            $log->new = ['printed' => true];
            $log->old = null;
            $log->save();
        }
    }

    /**
     * Listen to created event
     *
     * @param Act $model
     */
    public function downloaded($model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model);
            $log->new = ['downloaded' => true];
            $log->old = null;
            $log->save();
        }
    }

    /**
     * Format date_expected_pass
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateExpectedPassAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format date_pass
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDatePassAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format date_expected_ready
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateExpectedReadyAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format date_ready
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateReadyAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format date_sent_email
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDateSentEmailAttribute($value)
    {
        return $this->formatDate($value);
    }

    /**
     * Format cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCostAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format discount
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDiscountAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
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

        $data['attachments'] = $model->attachments->map(function($file) {
            return sprintf('%s (%0.1fKb)', $file->name, $file->size / 1024);
        })->toArray();

        return $data;
    }
}
