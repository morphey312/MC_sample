<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Contracts\Services\Voip\SubResolver;
use App\V1\Models\SiteEnquiry\Service;
use Illuminate\Support\Arr;
use App\V1\Traits\Models\HasCachedAttributes;
use App\V1\Traits\Models\EnquiryOperator;

class SiteEnquiry extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use HasCachedAttributes;
    use EnquiryOperator;

    const STATUS_NEW = 'new';
    const STATUS_PROCESSED = 'processed';
    const STATUS_NOT_PROCESSED = 'not_processed';
    const STATUS_IMPROCESSIBLE = 'improcessible';

    const PAYMENT_STATUS_PAYED = 'paid';
    const PAYMENT_STATUS_UNPAID = 'unpaid';

    const CATEGORY_COVID = 'covid-test';
    const CATEGORY_TOMOGRAPHY = 'tomography';
    const CATEGORY_RENTGEN = 'rentgen';
    const CATEGORY_PAYMENT = 'payment';

    const RELATION_TYPE = 'site_enquiry';

    /**
     * @var array
     */
    protected $fillable = [
        'category',
        'name',
        'email',
        'phone_number',
        'card_number',
        'clinic_id',
        'specialization_id',
        'doctor_id',
        'operator_id',
        'subject',
        'notes',
        'date',
        'cost',
        'payment_status',
        'services',
        'order_id',
        'referer',
        'client_id',
        'token',
        'appointment_id',
        'client_id',
        'token',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->resolveEnquirer();
            $model->assignToOperator();
            if ($model->category === self::CATEGORY_PAYMENT) {
                $model->bindServicesToAppointment();
            }
        });
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Related operator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
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
     * Related process record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function process()
    {
        return $this->hasOne(Call\ProcessLog::class, 'enquiry_id')->orderBy('id', 'asc');
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Resolve patient by phone number
     */
    public function resolveEnquirer()
    {
        if ($this->phone_number) {
            $subResolver = app(SubResolver::class);
            $enquirerInfo = $subResolver->resolve($this->phone_number);
            if ($enquirerInfo['subject'] instanceof Patient) {
                $this->patient = $enquirerInfo['subject'];
            }
        }
    }

    /**
     * Get category_group attribute
     *
     * @return string
     */
    public function getCategoryGroupAttribute()
    {
        switch ($this->category) {
            case static::CATEGORY_COVID:
                return EmployeeSiteEnquiryCategory::CATEGORY_COVID;
            case static::CATEGORY_TOMOGRAPHY:
                return EmployeeSiteEnquiryCategory::CATEGORY_TOMOGRAPHY;
            case static::CATEGORY_RENTGEN:
                return EmployeeSiteEnquiryCategory::CATEGORY_RENTGEN;
            default:
                return EmployeeSiteEnquiryCategory::CATEGORY_DEFAULT;
        }
    }

    /**
     * Related enquiry services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'site_enquiry_id');
    }

    /**
     * Related available enquiry services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function available_services()
    {
        return $this->services()->whereNull('appointment_id');
    }

    /**
     * Save related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updateServices($services)
    {
        foreach ($services as $item) {
            $service = Service::find(Arr::get($item, 'id'));
            $service->fill($item);
            $service->save();
        }
    }

    /**
     * Get payed attribute
     *
     * @return int
     */
    public function getPayedAttribute()
    {
        return $this->services->sum('payed_amount');
    }

    /**
     * Setup appointment id on services
     */
    protected function bindServicesToAppointment()
    {
        if ($this->appointment_id && $this->relationLoaded('services')) {
            $services = $this->getRelation('services');
            foreach ($services as $service) {
                $service->appointment_id = $this->appointment_id;
            }
        }
    }
}
