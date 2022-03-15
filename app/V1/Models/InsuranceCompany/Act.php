<?php

namespace App\V1\Models\InsuranceCompany;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\InsuranceCompany;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Traits\Models\ModelNumber;

class Act extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use ModelNumber;

    const RELATION_TYPE = 'insurance_act';
    const ACT_STATUS_CREATED = 'created';
    const ACT_STATUS_PAYED = 'payed';
    const ACT_STATUS_PARTLY = 'partly_payed';
    const ACT_PAYMENT_STATUS_NEW = 'new';
    const ACT_PAYMENT_STATUS_PAYED = 'payed';
    const ACT_PAYMENT_STATUS_PARTLY = 'partly_payed';

    /**
     * @var string
     */ 
    protected $table = 'insurance_acts';

    /**
     * @var array
     */
    protected $fillable = [
        'number',
        'amount',
        'insurance_company_id',
        'clinic_id',
        'comment',
        'act_services',
        'status',
        'payment_status',
        'payment_date',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->pickNumber();
        });
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related insurance company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurance_company()
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
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
     * Related act services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function act_services()
    {
        return $this->hasMany(Act\Service::class, 'act_id');
    }
}
