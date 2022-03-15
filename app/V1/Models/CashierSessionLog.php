<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use Auth;

class CashierSessionLog extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'start',
        'end',
    ];
    
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->employee = Auth::user()->getEmployeeModel();
        });

        static::updated(function ($model) {
            if ($model->end != null) {
                $model->clearCashierTokenMoney();
                $model->clearCashierCashRest();
            }
        });
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Update fiscal and uses cash cashbox initial_amount after session closed
     */
    public function clearCashierTokenMoney()
    {
        $employee = $this->employee()->with([
            'cashboxes.payment_method', 
            'cashboxes' => function($query) {
                $query->whereHas('payment_method', function($query) {
                    $query->where('use_cash', '=', 1);
                });
                $query->whereHas('payment_method.fiscal_clinics');
            },
        ])->first();
        $primaryClinic = $employee->employee_clinics()->where('is_primary', '=', 1)->first();
        $primaryClinicId = $primaryClinic ? $primaryClinic->clinic_id : null;
        
        if ($primaryClinicId) {
            $cashbox = $employee->cashboxes->first(function($item) use ($primaryClinicId) {
                return $primaryClinicId === $item->clinic_id;
            });
        } else {
            $cashbox = $employee->cashboxes->first();
        }
        
        if ($cashbox != null) {
            $cashbox->initial_amount = 0;
            $cashbox->save();
        }
    }

    public function clearCashierCashRest()
    {
        $employee = $this->employee()->with([
            'cashboxes' => function($query) {
                $query->whereHas('payment_method', function($query) {
                    $query->where('use_cash', '=', 1);
                });
            },
        ])->first();
        
        if ($employee->cashboxes->isNotEmpty()) {
            $employee->cashboxes->each(function($cashbox) {
                $cashbox->income = 0;
                $cashbox->expense = 0;
                $cashbox->save();
            });
        }
    }
}
