<?php

namespace App\V1\Observers\Audit;

use Closure;
use Illuminate\Database\Eloquent\Model;
use App\V1\Models\ActionLog;
use App\V1\Models\Patient;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Auth;
use Exception;
use Log;

class BaseAudit
{
    /**
     * @var bool
     */
    protected static $auditEnabled = true;

    /**
     * @var array|null
     */
    protected $attributes = null;

    /**
     * Enable/disable audit
     *
     * @param bool $flag
     */
    public static function enableAudit($flag = true)
    {
        self::$auditEnabled = $flag;
    }

    /**
     * Listen to created event
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        if (self::$auditEnabled && Auth::id()) {
            $this->safely(function() use($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
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
            $this->safely(function() use($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
                $old = $this->getOriginalValues($model);
                $new = $this->getCurrentValues($model);
                $changed = $this->getChangedAttributes($new, $old);
                if (count($changed) !== 0) {
                    $log->new = Arr::only($new, $changed);
                    $log->old = Arr::only($old, $changed);
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
            $this->safely(function() use($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
                $log->old = $this->getOriginalValues($model);
                $log->new = null;
                $log->save();
            });
        }
    }

    /**
     * Associate log record with model
     *
     * @param ActionLog $log
     * @param BaseModel $model
     */
    protected function associate($log, $model)
    {
        $log->loggable()->associate($model);
    }

    /**
     * Get original values from the model
     *
     * @param BaseModel $model
     *
     * @return array
     */
    protected function getOriginalValues($model)
    {
        return $this->formatAttributes(
            $this->pickAttributes($model->getOriginal(), $model),
            $model
        );
    }

    /**
     * Get current values from the model
     *
     * @param BaseModel $model
     *
     * @return array
     */
    protected function getCurrentValues($model)
    {
        return $this->formatAttributes(
            $this->pickAttributes($model->getAttributes(), $model),
            $model
        );
    }

    /**
     * Format attributes
     *
     * @param array $data
     * @param BaseModel $model
     *
     * @return array
     */
    protected function formatAttributes($data, $model)
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            $formatter = sprintf('format%sAttribute', ucfirst(camel_case($key)));
            if (method_exists($this, $formatter)) {
                $formatted[$key] = $this->{$formatter}($value, $key, $model);
            } else {
                $formatted[$key] = $value;
            }
        }
        return $formatted;
    }

    /**
     * Get attributes that were changed
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected function getChangedAttributes($new, $old)
    {
        $result = [];
        $newKeys = array_keys($new);
        $oldKeys = array_keys($old);
        foreach (array_diff($newKeys, $oldKeys) as $newKey) {
            $result[] = $newKey;
        }
        foreach (array_diff($oldKeys, $newKeys) as $removedKey) {
            $result[] = $removedKey;
        }
        foreach (array_intersect($newKeys, $oldKeys) as $sharedKey) {
            if (is_scalar($new[$sharedKey]) && is_scalar($old[$sharedKey])) {
                if (strval($old[$sharedKey]) !== strval($new[$sharedKey])) {
                    $result[] = $sharedKey;
                }
            } elseif (is_array($new[$sharedKey]) && is_array($old[$sharedKey])) {
                if (!$this->arraysEqual($old[$sharedKey], $new[$sharedKey])) {
                    $result[] = $sharedKey;
                }
            } elseif ($old[$sharedKey] !== $new[$sharedKey]) {
                $result[] = $sharedKey;
            }
        }
        return $result;
    }

    /**
     * Pick auditable attributes
     *
     * @param array $data
     * @param BaseModel $model
     *
     * @return array
     */
    protected function pickAttributes($data, $model)
    {
        if (is_array($this->attributes)) {
            return Arr::only($data, $this->attributes);
        }
        return Arr::only($data, $model->getFillable());
    }

    /**
     * Check if two arrays are equal
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return bool
     */
    protected function arraysEqual($arr1, $arr2)
    {
        return count($arr1) === count($arr2)
            && array_diff($arr1, $arr2) === [];
    }

    /**
     * Fetch attribute value from model
     *
     * @param string $class
     * @param string|int $key
     * @param string $attribute
     *
     * @return string
     */
    protected function fetchAttribute($class, $key, $attribute)
    {
        if (is_array($key)) {
            return $class::findMany($key)->pluck($attribute)->all();
        } else {
            return $key ? object_get($class::find($key), $attribute) : null;
        }
    }

    /**
     * Format patient name for log entry
     *
     * @param int $patientId
     *
     * @return string
     */
    protected function formatPatientName($patientId)
    {
        $patient = Patient::with('cards')->where('id', '=', $patientId)->first();

        if ($patient === null) {
            return null;
        }

        $card = $patient->cards->first();

        if ($card === null) {
            return $patient->full_name;
        }

        return sprintf('%s (%s)', $patient->full_name, $card->number);
    }

    /**
     * Format date value
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatDate($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    /**
     * Format time value
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatTime($value)
    {
        if ($value) {
            return implode(':', array_slice(explode(':', $value), 0, 2));
        }
        return null;
    }

    /**
     * Run function safely
     *
     * @param Closure $closure
     */
    protected function safely(Closure $closure)
    {
        try {
            $closure();
        } catch (Exception $e) {
            Log::warning("Audit error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
