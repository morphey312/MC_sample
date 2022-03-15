<?php

namespace App\V1\Models\Clinic;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Clinic;
use App\V1\Models\Payment;
use App\V1\Models\MoneyRecieverCashbox;

class MoneyReciever extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    const RELATION_TYPE = 'clinic_money_reciever';

    /**
     * @var string
     */
    protected $table = 'money_recievers';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'signer',
        'official_additional',
        'signer_position',
        'bank',
        'bank_account',
        'edrpou',
    ];

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'money_reciever_id');
    }

    /**
     * Related payments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'money_reciever_id');
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * Related money reciever cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function money_reciever_cashboxes()
    {
        return $this->hasMany(MoneyRecieverCashbox::class, 'money_reciever_id');
    }
}
