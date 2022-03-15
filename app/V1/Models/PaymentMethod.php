<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee\Cashbox;
use Illuminate\Support\Arr;

class PaymentMethod extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const RELATION_TYPE = 'payment_method';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'use_cash',
        'is_enabled',
        'for_checkbox',
        'online_payment',
        'pc_payment',
        'clinics'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'use_cash' => 'boolean',
        'is_enabled' => 'boolean',
        'for_checkbox' => 'boolean',
        'online_payment' => 'boolean',
        'pc_payment' => 'boolean'
    ];

    /**
     * @var array
     */
    public $clinicsToSave = null;

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->clinicsToSave !== null) {
                $model->saveClinics($model->clinicsToSave);
            }
        });
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_payment_method', 'payment_method_id', 'clinic_id')
            ->orderBy('name')
            ->withPivot('is_fiscal');
    }

    /**
     * Related clinics where method is fiscal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fiscal_clinics()
    {
        return $this->clinics()->wherePivot('is_fiscal', 1);
    }

    /**
     * Related cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cashboxes()
    {
        return $this->hasMany(Cashbox::class, 'payment_method_id');
    }

    /**
     * Save clinics
     *
     * @param array $data
     */
    public function saveClinics(array $data)
    {
        $this->clinics()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['clinic_id'],
                'data' => Arr::only($item, ['is_fiscal']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Set clinics to save
     *
     * @param mixed $value
     */
    public function setClinicsAttribute($value)
    {
        $this->clinicsToSave = $value;
    }
}
