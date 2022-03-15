<?php

namespace App\V1\Models\Analysis\Laboratory\Order\Item;

use App\V1\Contracts\Repositories\Analysis\Laboratory\Order\ItemRepository;
use App\V1\Models\Analysis\Laboratory\Order\Item;
use App\V1\Models\Analysis\Laboratory\TransferSheet;
use App\V1\Models\BaseModel;
use App\V1\Models\Analysis\Laboratory\Order;
use App\V1\Models\Analysis\Result as AnalysisResult;
use App\V1\Models\Patient;
use App\V1\Models\Clinic;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Container extends BaseModel implements ClinicShared
{
    /**
     * @var array
    */
    protected $table = 'laboratory_order_item_containers';

    /**
     * @var array
    */
    protected $fillable = [
        'item_id',
        'name',
        'random',
        'barcode',
        'measure',
        'image_data',
        'container_id',
        'biomaterial_id',
        'transfer_id',
        'handbook_measure',

    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $repository = app(ItemRepository::class);

            $olditems = $repository->getByResultId($model->result_id);
            foreach ($olditems as $item) {
                $repository->delete($item);
            }
        });
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->item->analysis_result->clinic_id];
    }

    /**
     * Related item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Related transfer()
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transfer_sheet()
    {
        return $this->belongsTo(TransferSheet::class, 'transfer_id');
    }
}
