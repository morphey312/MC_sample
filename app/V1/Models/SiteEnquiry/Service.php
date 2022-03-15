<?php

namespace App\V1\Models\SiteEnquiry;

use App\V1\Models\BaseModel;
use App\V1\Models\SiteEnquiry;
use App\V1\Models\Employee;
use App\V1\Models\WaitListRecord;
use Illuminate\Support\Facades\Auth;

class Service extends BaseModel
{
    const STATUS_TO_REFUND = 'to_refund';
    const STATUS_REFUNDED = 'refunded';

    /**
     * @var string
     */
    protected $table = 'site_enquiry_services';

    /**
     * @var array
     */
    protected $fillable = [
        'service_id',
        'service_type',
        'site_enquiry_id',
        'cost',
        'discount',
        'payed_amount',
        'appointment_id',
        'refund_status',
        'refunder_id',
        'wait_list_record_id',
        'is_prepayment',
    ];

    /**
     * @var array
     *
    */
    protected $casts = [
        'is_prepayment' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->refund_status === static::STATUS_REFUNDED) {
                $model->refunder = Auth::user()->getEmployeeModel();
            }
        });
    }

    /**
     * Related site enquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site_enquiry()
    {
        return $this->belongsTo(SiteEnquiry::class, 'site_enquiry_id');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function service()
    {
        return $this->morphTo();
    }

    /**
     * Related creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function refunder()
    {
        return $this->belongsTo(Employee::class, 'refunder_id');
    }

    /**
     * Related wait_list_record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wait_list_record()
    {
        return $this->belongsTo(WaitListRecord::class, 'wait_list_record_id');
    }
}
