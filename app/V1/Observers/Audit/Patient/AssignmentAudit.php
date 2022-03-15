<?php

namespace App\V1\Observers\Audit\Patient;

use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use App\V1\Models\ActionLog;
use App\V1\Models\Patient;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Auth;
use Exception;
use Log;

class AssignmentAudit extends BaseAudit
{
    /**
     * Get current values from the model
     *
     * @param BaseModel $model
     *
     * @return array
     */
    protected function getCurrentValues($model)
    {
        return ['assigned_medicines' => $this->formatData($model->assigned_medicines)];
    }

        /**
     * Listen to updating event
     *
     * @param Model $model
     */
    public function saving(Model $model)
    {
        if (self::$auditEnabled && $model->exists && Auth::id() && $model->recordable_type === 'card_assignment') {
            $this->safely(function() use($model) {
                $log = new ActionLog();
                $log->user_id = Auth::id();
                $this->associate($log, $model);
                $data = $this->getCurrentValues($model->recordable);
                $log->new = $data;
                $log->save();
            });
        }
    }

    private function formatData($data){
        $items = [];
        if (!empty($data)) {
           $items = collect($data)->map(function($item) {
                return
                PHP_EOL .
                'Название: ' . $item->medicine->name .   PHP_EOL .
                'Количество: ' . round($item->quantity) .   PHP_EOL .
                'Бесплатный: ' . $item->is_free .   PHP_EOL .
                'Комментарий: ' . $item->comment .   PHP_EOL .
                'Длительность приёма: ' . (int)$item->medication_duration . '
                ' .PHP_EOL ;
            })->all();
        }
        return implode("", $items);
    }
}
