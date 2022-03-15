<?php

namespace App\V1\Observers\Audit\Analysis;

use App\V1\Models\ActionLog;
use App\V1\Models\Analysis\Result;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Models\InsuranceCompany\Act;
use App\V1\Models\Patient;
use App\V1\Repositories\Query\DaySheet\LockFilter;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Auth;
use Exception;
use Log;
use Illuminate\Database\Eloquent\Model;
use App\V1\Observers\Audit\BaseAudit;

class ResultCardRecordAppointmentAudit extends BaseAudit
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
                if($model->recordable_type === Patient\Card\Record::TYPE_CARD_ASSIGNMENT && $model->recordable->type === Patient\Card\Assignment::ANALYSIS_RESULTS){
                    foreach ($model->recordable->analysis_results  as $analysis_result){
                        $log = new ActionLog();
                        $log->user_id = Auth::id();

                        $this->associate($log, $model->appointment);
                        $log->new = $this->getCurrentValues($analysis_result);
                        $log->old = null;
                        $log->save();
                    }
                }
            });
        }
    }

    /**
     * Listen to updating event
     *
     * @param Model $model
     */

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
            + ['old_analysis' => $fresh->analysis->name, 'old_assigner' => $this->formatAssignerIdAttribute($model->assigner_id)];
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + ['new_analysis' => $model->analysis->name, 'new_assigner' => $this->formatAssignerIdAttribute($model->assigner_id)];
    }

    /**
     * @inherit
     */
    protected function getChangedAttributes($new, $old)
    {
        $changed = parent::getChangedAttributes($new, $old);

        if (count($changed) === 4) {
            if ($new['old_assigner_id'] == $old['new_assigner_id']) {
                return [];
            }
            // only old_analysis and new_analysis
            if ($new['new_analysis'] == $old['old_analysis']) {
                return [];
            }
        }

        return $changed;
    }
}
