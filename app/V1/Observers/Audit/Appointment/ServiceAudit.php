<?php

namespace App\V1\Observers\Audit\Appointment;

use App\V1\Models\Appointment\Service;
use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\ActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\V1\Models\Price;

class ServiceAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'price_id',
        'cost',
        'quantity',
        'expected_payment',
        'discount',
        'is_base',
        'not_debt',
        'by_policy',
        'franchise',
        'warranter',
    ];

    /**
     * Listen to created event
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (self::$auditEnabled && Auth::id() && $this->isNotContainer($model)) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->appointment);
            $log->new = $this->getCurrentValues($model);
            $log->old = null;
            $log->save();
        }
    }


    /**
     * Listen to updating event
     *
     * @param Model $model
     */
    public function saving(Model $model)
    {
        if (self::$auditEnabled && $model->exists && Auth::id() && ($this->isNotContainer($model) || $model->isDirty('not_debt'))) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            if ($this->isNotContainer($model) && !$model->isDirty('not_debt')) {
                $this->associate($log, $model->appointment);
                $old = $this->getOriginalValues($model);
                $new = $this->getCurrentValues($model);
                $changed = $this->getChangedAttributes($new, $old);
                if (count($changed) !== 0) {
                    $log->new = Arr::only($new, $changed);
                    $log->old = Arr::only($old, $changed);
                    $log->save();
                }
            } else if ($model->isDirty('not_debt')) {
                $this->associate($log, $model);
                $log->new = ['not_debt' => $model->fresh()->not_debt];
                $log->old = ['not_debt' => $model->not_debt];
                $log->save();
            }
        }
    }

    /**
     * Listen to deleting event
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
        if (self::$auditEnabled && Auth::id() && $this->isNotContainer($model)) {
            $log = new ActionLog();
            $log->user_id = Auth::id();
            $this->associate($log, $model->appointment);
            $log->old = $this->getOriginalValues($model);
            $log->new = null;
            $log->save();
        }
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + ['old_service' => $fresh->service->name];
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + ['new_service' => $model->service->name];
    }

    /**
     * @inherit
     */
    protected function getChangedAttributes($new, $old)
    {
        $changed = parent::getChangedAttributes($new, $old);

        if (count($changed) === 2) {
            // only old_service and new_service
            if ($new['new_service'] == $old['old_service']) {
                return [];
            }
        }

        return $changed;
    }

    protected function isNotContainer($model)
    {
        return $model->appointment_id != null && $model->container_type == null;
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
     * Format not_debt
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatNotDebtAttribute($value)
    {
        return (bool) $value;
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
        return (bool) $value;
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
        return (float) $value;
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
        return (int) $value;
    }

    /**
     * Format self_cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatExpectedPaymentAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
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
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format pric_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatPriceIdAttribute($value)
    {
        return $this->fetchAttribute(Price::class, $value, 'cost');
    }
}
