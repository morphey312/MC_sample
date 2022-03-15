<?php

namespace App\V1\Models\Employee\Cashbox;

use App\V1\Models\BaseModel;
use  App\V1\Models\Employee;
use  App\V1\Models\Employee\Cashbox;
use  App\V1\Exceptions\CashTransferException;

class CashTransfer extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'cashier_id',
        'source_id',
        'destination_id',
        'amount',
        'comment',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->updateCashboxes();
        });
    }

    /**
     * Related cashier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    /**
     * Related source
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(Cashbox::class, 'source_id');
    }

    /**
     * Related source
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destination()
    {
        return $this->belongsTo(Cashbox::class, 'destination_id');
    }

    /**
     * Update related cahboxes
     */
    public function updateCashboxes()
    {
        if ($this->source_id != null && $this->destination_id != null) {
            $this->updateTransferedCashboxes();
        } else {
            if ($this->source_id != null) {
                $this->updateSource();
            }

            if ($this->destination_id != null) {
                $this->updateDestination();
            }
        }
    }

    /**
     * Update related source cashbox
     */
    protected function updateSource()
    {
        $source = $this->source;

        if ($source->income >= $this->amount) {
            $source->income = $source->income - $this->amount;
            $source->save();
        } else {
            $difference = $this->amount - $source->income;
            $initialAmount = $source->initial_amount - $difference;

            if ($initialAmount >= 0) {
                $source->income = 0;
                $source->initial_amount = $initialAmount;
                $source->save();
            } else {
                $this->delete();
                throw new CashTransferException('Cashbox amount is less than transfer amount');
            }
        }
    }

    /**
     * Update related destination cashbox
     */
    protected function updateDestination()
    {
        $destination = $this->destination;
        $destination->initial_amount = $destination->initial_amount + $this->amount;
        $destination->save();
    }

    /**
     * Update related source and destination cashboxes
     */
    protected function updateTransferedCashboxes()
    {
        $source = $this->source;
        $destination = $this->destination;
        $rest = ($source->income - $source->expense - $this->amount);

        if ($rest >= 0) {
            $source->income = $source->income - $this->amount;
            $destination->income = $destination->income + $this->amount;
            $source->save();
            $destination->save();
        } else {
            $this->delete();
            throw new CashTransferException('Cashbox amount is less than transfer amount');
        }
    }
}
