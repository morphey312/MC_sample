<?php

namespace App\V1\Models\Analysis\Laboratory\Order;

use App\V1\Contracts\Repositories\Analysis\Laboratory\Order\ItemRepository;
use App\V1\Models\Analysis\Laboratory\Order\Item\Container;
use App\V1\Models\BaseModel;
use App\V1\Models\Analysis\Laboratory\Order;
use App\V1\Models\Analysis\Result as AnalysisResult;
use App\V1\Models\Patient;
use App\V1\Models\Clinic;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Item extends BaseModel implements ClinicShared
{

    /**
     * @var array
    */
    protected $table = 'laboratory_order_items';

    /**
     * @var array
    */
    protected $fillable = [
        'results',
        'patient_id',
        'comment',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        /*
        static::creating(function ($model) {
            $repository = app(ItemRepository::class);

            $olditems = $repository->getByResultId($model->result_id);
            foreach ($olditems as $item) {
                $repository->delete($item);
            }
        });
        */
    }

    /**
     * Related analysis result
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function analysis_result()
    {
        return $this->belongsTo(AnalysisResult::class, 'result_id');
    }

    /**
     * Related analysis result
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->analysis_result->clinic_id];
    }
}
