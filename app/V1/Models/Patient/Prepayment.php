<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Specialization;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;
use App\V1\Models\Payment;
use App\V1\Models\Service;
use App\V1\Models\Payment\Check;
use App\V1\Jobs\SendOneSTransactions;

class Prepayment extends BaseModel implements ClinicShared, SharedResourceInterface
{
    use SharedResource;

    const RELATION_TYPE = 'patient_prepayment';

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_prepayments';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'amount',
        'patient_id',
        'clinic_id',
        'specialization_id',
        'service_id',
        'payment_id',
        'used',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'used' => 'boolean',
    ];

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
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Related service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Related payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
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
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Attach payment
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function createPayment($attributes, $check = false)
    {
        $payment = new Payment($attributes);
        $payment->payed_amount = $this->amount;
        $payment->is_prepayment = true;
        $payment->is_deposit = true;
        $payment->type = Payment::TYPE_INCOME;
        if($check) {
            $check = new Check();
            $check->save();
            $payment->check_id = $check->id;
        }
        $payment->save();
        if (config('services.one_s.enable_transaction_send') == true) {
            SendOneSTransactions::dispatch([$payment]);
        }
        return $payment;
    }
}
