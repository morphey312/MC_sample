<?php

namespace App\V1\Models\PriceAgreementAct;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\PriceAgreementAct;
use Illuminate\Support\Arr;

class Price extends BaseModel
{
    const CHANGE_COST = 'change_cost';
    const CLOSE_PRICE = 'close_price';
    const ADD_CLINIC = 'add_clinic';
    const NEW_PRICE = 'new_price';
    const SERVICE_TYPE = 'service';
    const ANALYSIS_TYPE = 'analysis';

    /**
     * Specify model table
     */
    protected $table = 'price_agreement_act_prices';

    /**
     * @var array
     */
    protected $fillable = [
        'price_id',
        'service_type',
        'service_id',
        'change_type',
        'cost',
        'price_agreement_act_id',
        'recommended_cost',
        'currency',
        'clinics',

    ];

    /**
     * @var array
     */
    protected $casts = [
        'recommended_cost' => 'float',
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
     * Set clinics to save
     *
     * @param mixed $value
     */
    public function setClinicsAttribute($value)
    {
        $this->clinicsToSave = $value;
    }

    /**
     * Related service/analysis
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function service()
    {
        return $this->morphTo();
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'price_agreement_act_price_clinics', 'act_price_id', 'clinic_id')
                    ->withPivot('code', 'duration_days');
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
                'data' => Arr::only($item, ['code', 'duration_days']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Related price
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function price()
    {
        return $this->belongsTo(\App\V1\Models\Price::class, 'price_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * Related price agreement act
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function price_agreement_act()
    {
        return $this->belongsTo(PriceAgreementAct::class, 'price_agreement_act_id');
    }
}
