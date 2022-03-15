<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\DiscountCardType\NumberingKind;
use Illuminate\Support\Arr;

class DiscountCardType extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'use_detail_payments',
        'discount_percent',
        'dont_use_for_patient',
        'show_card_in_patient_list',
        'type_icon_id',
        'cant_be_copied',
        'propose_to_disable_on_copy',
        'max_owners',
        'dont_auto_add_to_appointment',
        'priority',
        'expire_period',
        'use_card_number',
        'number_kind_id',
        'clinics',
        'payment_destinations',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'use_detail_payments' => 'boolean',
        'dont_use_for_patient' => 'boolean',
        'show_card_in_patient_list' => 'boolean',
        'cant_be_copied' => 'boolean',
        'propose_to_disable_on_copy' => 'boolean',
        'dont_auto_add_to_appointment' => 'boolean',
        'use_card_number' => 'boolean',
    ];

    /**
     * @var array
     */
    public $clinicsToSave = null;

    /**
     * @var array
     */
    public $paymentDestinationsToSave = null;

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
            if ($model->paymentDestinationsToSave !== null) {
                $model->savePaymentDestinations($model->paymentDestinationsToSave);
            }
        });
    }

    /**
     * Related card numbering kind
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function number_kind()
    {
        return $this->belongsTo(NumberingKind::class, 'number_kind_id');
    }

    /**
     * Related payment destinations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payment_destinations()
    {
        return $this->belongsToMany(Service\PaymentDestination::class, 'discount_card_payment', 'discount_card_type_id', 'payment_destination_id')
                    ->withPivot('discount_percent', 'date_start', 'date_end');
    }

    /**
     * Save service payment destinations
     *
     * @param array $data
     */
    public function savePaymentDestinations(array $data)
    {
        $this->payment_destinations()->detach();

        if (empty($data)) {
            return;
        }

        foreach ($data as $payment) {
            $fields = Arr::only($payment, ['discount_percent', 'date_start', 'date_end']);
            $this->payment_destinations()->attach(Arr::get($payment, 'payment_destination_id'), $fields);
        }
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_discount_card', 'discount_card_type_id', 'clinic_id')
                    ->orderBy('name')
                    ->withPivot('payment_method_id');
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
                'data' => Arr::only($item, ['payment_method_id']),
            ];
        }, $data), 'data', 'id'));
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
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
     * Set payment_destinations to save
     *
     * @param mixed $value
     */
    public function setPaymentDestinationsAttribute($value)
    {
        $this->paymentDestinationsToSave = $value;
    }
}
