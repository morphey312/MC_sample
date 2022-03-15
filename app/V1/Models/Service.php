<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Models\HasActPrice;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasPrice;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Models\Appointment;
use Illuminate\Support\Arr;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Service extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasPrice, HasConstraint;

    const RELATION_TYPE = 'service';

    const SERVICE_MARK_ANALYSIS = 'for_analyses';

    /**
     * @var array
     */
    protected $fillable = [
        'ehealth_service_id',
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'name_ua',
        'name_ua_lc1',
        'name_ua_lc2',
        'name_ua_lc3',
        'specialization_id',
        'disabled',
        'for_prepayment',
        'is_no_auto_recommend_source',
        'is_for_discount_card',
        'is_base',
        'is_online',
        'clinics',
        'payment_destination_id',
        'diagnosis_id',
        'site_service_type',
        'for_foreigners',
    ];

    /**
     * @var array
     *
    */
    protected $casts = [
        'disabled' => 'boolean',
        'is_for_discount_card' => 'boolean',
        'is_no_auto_recommend_source' => 'boolean',
        'is_base' => 'boolean',
        'for_prepayment' => 'boolean',
        'is_online' => 'boolean',
        'is_debt' => 'boolean',
        'for_foreigners' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'appointment_services',
        'assigned_services',
        'site_enquiry_services',
        'prices',
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
     * Related ehealth service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ehealth_service()
    {
        return $this->belongsTo(Ehealth\Service::class, 'ehealth_service_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'service_clinics', 'service_id', 'clinic_id')
                    ->withPivot('code');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related payment destination
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_destination()
    {
        return $this->belongsTo(Service\PaymentDestination::class, 'payment_destination_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_services', 'service_id', 'appointment_id');
    }

    /**
     * Related protocol templates
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function protocol_templates()
    {
        return $this->belongsToMany(Patient\Card\ProtocolTemplate::class, 'protocol_template_services', 'service_id', 'template_id');
    }

    /**
     * Related appointment services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_services()
    {
        return $this->hasMany(Appointment\Service::class, 'service_id');
    }

    /**
     * Related assigned services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_services()
    {
        return $this->hasMany(Patient\AssignedService::class, 'service_id');
    }

    /**
     * Related site enquiry services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function site_enquiry_services()
    {
        return $this->hasMany(SiteEnquiry\Service::class, 'service_id');
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
                'data' => Arr::only($item, ['code']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * @inherit
     */
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
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function act_prices()
    {
        return $this->morphMany(\App\V1\Models\PriceAgreementAct\Price::class, 'service')->with('clinics');
    }
}
