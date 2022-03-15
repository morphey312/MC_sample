<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee\Cashbox;
use App\V1\Models\Service\PaymentDestination;
use App\V1\Models\Appointment\Service as AppointmentService;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Exceptions\PatientDepositException;
use App\V1\Exceptions\PaymentException;
use App\V1\Traits\Models\HasCachedAttributes;
use App\V1\Models\Clinic\MoneyReciever;
use App\V1\Models\Specialization\Clinic as SpecializationClinic;
use App\V1\Jobs\SendOneSTransactions;
use Carbon\Carbon;
use App\V1\Contracts\Repositories\PaymentRepository;
use App\V1\Contracts\Repositories\Specialization\ClinicRepository as SpecializationClinicRepository;

class Payment extends BaseModel implements ClinicShared, SharedResourceInterface
{
    use SharedResource;
    use HasCachedAttributes;

    const RELATION_TYPE = 'payment';

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const KIND_APPOINTMENT = 'has_appointment';
    const KIND_DEPOSIT = 'deposit';
    const PATIENT_DEPOSIT = 'Аванс';
    const DEPOSIT_SERVICE = 'Попередня оплата за медичні послуги';
    const BASE_CURRENCY_CODE = 'uah';

    /**
     * @var array
     */
    protected $fillable = [
        'amount',
        'payed_amount',
        'discount',
        'cashbox_id',
        'service_id',
        'doctor_id',
        'clinic_id',
        'money_reciever_id',
        'cashier_id',
        'patient_id',
        'appointment_id',
        'payment_destination_id',
        'type',
        'payment_check',
        'is_technical',
        'is_prepayment',
        'is_deposit',
        'is_cash',
        'from_deposit',
        'is_deleted',
        'timestamp',
        'comment',
        'created_at',
        'checkbox_money_reciever_id',
        'money_reciever_cashbox_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_deposit' => 'boolean',
        'from_deposit' => 'boolean',
        'is_deleted' => 'boolean',
        'is_prepayment' => 'boolean',
        'is_technical' => 'boolean',
    ];

    /**
     * @var array
     */
    protected static $onlineCashier = null;

    /**
     * Related appointment service
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(AppointmentService::class, 'service_id');
    }

    /**
     * Related cashbox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashbox()
    {
        return $this->belongsTo(Cashbox::class, 'cashbox_id');
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
     * Related cashier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
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
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related payment destination
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_destination()
    {
        return $this->belongsTo(PaymentDestination::class, 'payment_destination_id');
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
     * Related cashbox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function money_reciever_cashbox()
    {
        return $this->belongsTo(MoneyRecieverCashbox::class, 'money_reciever_cashbox_id');
    }

    /**
     * Related payment check
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_check()
    {
        return $this->belongsTo(Payment\Check::class, 'check_id');
    }

    /**
     * Add entity amount to patient deposit balance
     */
    public function increasePatientDeposit()
    {
        if ($account = $this->patient->getClinicAccount($this->clinic_id)) {
            $account->balance = $account->balance + $this->amount;
            $account->save();
        } else {
            $this->patient->accounts()->create([
                'balance' => $this->amount,
                'clinic_id' => $this->clinic_id,
            ]);
        }
    }

    /**
     * Subtract entity amount from patient deposit balance
     */
    public function decreasePatientDeposit()
    {
        if ($account = $this->patient->getClinicAccount($this->clinic_id)) {
            $account->balance = $account->balance - $this->amount;
            $account->save();
        }
    }

    /**
     * Related treatment course
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cache_validity()
    {
        return $this->hasOne(CacheValidity::class, 'patient_id', 'patient_id');
    }

    /**
     * Verify patient has available deposit amount
     */
    public function verifyPatientDepositAvailable()
    {
        if ($account = $this->patient->getClinicAccount($this->clinic_id)) {
            if ($account->balance < $this->amount) {
                throw new PatientDepositException('Patient deposit balance is less than payment amount');
            }
        }
    }

    /**
     * Update related cashbox income or expense field
     */
    public function updateCashbox()
    {
        if ($this->timestamp != null) {
            return;
        }

        $cashbox = $this->cashbox;
        $currentAmount = $cashbox->{$this->type};
        $cashbox->{$this->type} = $currentAmount + $this->payed_amount;
        $cashbox->save();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Set is deleted to deposit return
     */
    public function deleteDepositReturn()
    {
        if ($timestamp = $this->timestamp) {
            $repository = app(PaymentRepository::class);
            $payments = $repository->all($repository->filter([
                'timestamp' => $timestamp,
                'payment_type' => static::TYPE_EXPENSE,
            ]));

            foreach ($payments as $payment) {
                $payment->is_deleted = true;
                $payment->save();
                if (config('services.one_s.enable_transaction_send') == true) {
                    SendOneSTransactions::dispatch([$payment]);
                }
            }
        }
    }

    /**
     * Decrease related cashbox income
     *
     * @param @mixed $cashbox
     */
    public function decreaseCashboxIncome($cashbox)
    {
        $cashbox->income = $cashbox->income - $this->payed_amount;
    }

    /**
     * Decrease related cashbox expense
     *
     * @param @mixed $cashbox
     */
    public function decreaseCashboxExpense($cashbox)
    {
        $cashbox->expense = $cashbox->expense - $this->amount;
    }

    /**
     * Increase related cashbox income
     *
     * @param @mixed $cashbox
     */
    public function increaseCashboxIncome($cashbox, $amount)
    {
        $cashbox->income = $cashbox->income + $amount;
        $cashbox->save();
    }

    /**
     * Verify cachbox changed on payment delete
     */
    public function verifyCashboxChange()
    {
        $prevModel = static::find($this->id);
        if ($prevModel->cashbox_id != $this->cashbox_id) {
            try {
                Cashbox::where('id', '=', $prevModel->cashbox_id)->decrement('income', $prevModel->payed_amount);
                Cashbox::where('id', '=', $this->cashbox_id)->increment('income', $this->payed_amount);
            } catch (PaymentException $e) {
                throw new PatientDepositException('Cant change payment cashbox');
            }
        }
        return;
    }

    /**
     * Update adjacent service expected payment by payment amount
     * @param bool $save
     */
    public function updateServiceExpectedPayment()
    {
        if ($this->type === static::TYPE_INCOME && $this->service) {
            $this->service->updateExpectedPayment($this->amount);
        }
    }

    /**
     * Get Online payments cashier ID and cashbox ID
     *
     * @return array
     */
    protected static function getOnlineCashierAndCashbox()
    {
        if (self::$onlineCashier === null) {
            self::$onlineCashier = [
                'cashier_id' => null,
                'cashbox_id' => null,
            ];

            $onlineCashier = Employee::getOnlinePaymentCashier();
            if ($onlineCashier !== null) {
                $serviceCashbox = $onlineCashier->cashboxes->first(function($cashbox) {
                    return $cashbox->payment_method != null &&
                        $cashbox->payment_method->online_payment == 1 &&
                        $cashbox->payment_method->use_cash == 0;
                });

                if ($serviceCashbox !== null) {
                    self::$onlineCashier['cashier_id'] = $onlineCashier->id;
                    self::$onlineCashier['cashbox_id'] = $serviceCashbox->id;
                }
            }
        }

        return self::$onlineCashier;
    }

    /**
     * Check if single payment is online payment
     *
     * @return bool
     */
    public function isOnlinePayment()
    {
        $onlineCashier = self::getOnlineCashierAndCashbox();
        return $this->cashier_id === $onlineCashier['cashier_id']
            && $this->cashbox_id === $onlineCashier['cashbox_id'];
    }

    /**
     * Get patient card number attribute
     *
     * @return mixed
     */
    public function getCardNumberAttribute()
    {
        if ($this->service && $this->service->card_specialization_id != null) {
            return $this->patient->getCardNumber($this->clinic_id, $this->service->card_specialization_id);
        } elseif ($this->appointment_id != null) {
            return $this->appointment->card_number;
        }
        return null;
    }

    /**
     * Set money reciever
     *
     * @return mixed
     */
    public function setMoneyReciever()
    {
        if ($this->service && $this->service->service) {
            $genericService = $this->service->service;
            $clinicRepository = app(SpecializationClinicRepository::class);
            $specializationClinic = $clinicRepository->getFilteredQuery([
                'has_money_reciever' => 1,
                'clinic' => $this->clinic_id,
                'specialization' => $genericService->specialization_id,
            ])->first();

            if ($specializationClinic) {
                $this->money_reciever_id = $specializationClinic->money_reciever_id;
                return;
            }
        }
        $this->money_reciever_id = $this->clinic->money_reciever_id;
        return;
    }

    /**
     * Related money_reciever
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function money_reciever()
    {
        return $this->belongsTo(Clinic\MoneyReciever::class, 'money_reciever_id');
    }

    /**
     * Update payment cache validity
     */
    public function updateCacheValidity()
    {
        $this->cache_validity()->updateOrCreate(
            [],
            [
                'last_payment_action_timestamp' => Carbon::now()->timestamp
            ]
        );
    }
}
