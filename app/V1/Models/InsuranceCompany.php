<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Models\Price\Set as PriceSet;

class InsuranceCompany extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use HasConstraint;

    const RELATION_TYPE = 'insurer';
    const SET_TYPE = 'insurance';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'short_name',
        'location',
        'post_address',
        'phone_number',
        'bank_account',
        'egrpo',
        'tax_number',
        'signer',
        'signer_position',
        'show_price',
        'price_set',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'show_price' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'company_clinics',
        'price_set',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->hasPriceSet() || $model->show_price === false) {
                return;
            }
            $model->createPriceSet();
        });
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'insurance_company_clinics', 'insurance_company_id', 'clinic_id')
                    ->withPivot('agreement', 'agreement_active')
                    ->orderBy('name');
    }

    /**
     * Related company clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function company_clinics()
    {
        return $this->hasMany(InsuranceCompany\Clinic::class, 'insurance_company_id');
    }

    /**
     * Related price_set
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function price_set()
    {
        return $this->morphOne(PriceSet::class, 'owner');
    }

    /**
     * Related insurance policies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insurance_policies()
    {
        return $this->hasMany(Patient\InsurancePolicy::class, 'insurance_company_id');
    }

    /**
     * Create company price set
     */
    public function createPriceSet()
    {
        $this->price_set = new PriceSet(['type' => static::SET_TYPE]);
    }

    /**
     * Verify company has price_set
     *
     * @return bool
     */
    public function hasPriceSet()
    {
        return $this->price_set !== null;
    }

    /**
     * Get title attribute
     *
     * @return bool
     */
    public function getTitleAttribute()
    {
        return (strlen($this->short_name) === 0) ? $this->name : $this->short_name;
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
