<?php

namespace App\V1\Observers\Audit\Analysis;

use App\V1\Models\ActionLog;
use App\V1\Models\Analysis\Result;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Models\InsuranceCompany\Act;
use App\V1\Models\Patient;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Auth;
use Exception;
use Log;
use Illuminate\Database\Eloquent\Model;
use App\V1\Observers\Audit\BaseAudit;

class ResultAppointmentAudit extends BaseAudit
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


    public function created(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $this->safely(function () use ($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();

                if ($model->status === Result::STATUS_ASSIGNED && !$model->appointment_id) {
                    if($model->card_assignment->card_record){
                        $appointment = $model->card_assignment->card_record->appointment;
                    }else{
                        return false;
                    }
                } else {
                    $appointment = $model->appointment;
                }

                $this->associate($log, $appointment);
                $log->new = $this->getCurrentValues($model);
                $log->old = null;
                $log->save();
            });
        }
    }

    /**
     * Listen to updating event
     *
     * @param Model $model
     */
    public function saving(Model $model)
    {
        if (self::$auditEnabled && $model->exists && Auth::id()) {
            $this->safely(function () use ($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();

                if ($model->status === Result::STATUS_ASSIGNED && !$model->appointment_id) {
                    $appointment = $model->card_assignment->card_record->appointment;
                } else {
                    $appointment = Appointment::find($model->appointment_id);
                }
                $this->associate($log, $appointment);

                if ($model->isDirty('appointment_id')) {
                    $old = null;
                    $log->created_at = now()->addSeconds(10);
                } else {
                    $old = $this->getOriginalValues($model);
                }

                $new = $this->getCurrentValues($model);

                if (!$model->isDirty('appointment_id')) {
                    $changed = $this->getChangedAttributes($new, $old);
                    if (count($changed) !== 0) {
                        $log->new = Arr::only($new, $changed);
                        $log->old = Arr::only($old, $changed);
                        $log->save();
                    }
                } else {
                    $log->old = $old;
                    $log->new = $new;
                    $log->save();
                }
            });
        }
    }

    /**
     * Listen to deleting event
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $this->safely(function () use ($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model->appointment);
                $log->old = $this->getOriginalValues($model);
                $log->new = null;
                $log->save();
            });
        }
    }

    /**
     * Format quantity
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatQuantityAttribute($value)
    {
        return (int)$value;
    }

    /**
     * Format name
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNameAttribute($value)
    {
        return (string)$value;
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
        return number_format((float)$value, 2, '.', '');
    }

    /**
     * Format by_policy
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatByPolicyAttribute($value)
    {
        return (bool)$value;
    }

    /**
     * Format franchise
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatFranchiseAttribute($value)
    {
        return number_format((float)$value, 2, '.', '');
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
        return number_format((float)$value, 2, '.', '');
    }

    /**
     * Format doctor_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatAssignerIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh)
            + ['old_analysis' => $fresh->analysis->name, 'old_assigner' => $this->formatAssignerIdAttribute($model->assigner_id)];
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model)
            + ['new_analysis' => $model->analysis->name, 'new_assigner' => $this->formatAssignerIdAttribute($model->assigner_id)];
    }

    /**
     * @inherit
     */
    protected function getChangedAttributes($new, $old)
    {
        $changed = parent::getChangedAttributes($new, $old);

        if (count($changed) === 4) {
            if ($new['new_assigner'] == $old['old_assigner']) {
                return [];
            }
            // only old_analysis and new_analysis
            if ($new['new_analysis'] == $old['old_analysis']) {
                return [];
            }
        }

        return $changed;
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
        $data['attachments'] = $model->attachments->map(function ($file) {
            return sprintf('%s (%0.1fKb)', $file->name, $file->size / 1024);
        })->toArray();

        return $data;
    }
}
