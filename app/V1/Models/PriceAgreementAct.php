<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Chat\Room;
use App\V1\Repositories\EmployeeRepository;
use Illuminate\Support\Arr;

class PriceAgreementAct extends BaseModel
{
    const SERVICE_TYPE_SERVICE = Service::RELATION_TYPE;
    const SERVICE_TYPE_ANALYSIS = Analysis::RELATION_TYPE;

    const STATUS_AGREED = 'agreed';
    const STATUS_NOT_AGREED = 'not_agreed';
    const STATUS_IN_WORK = 'in_work';

    const TYPE_ANALYSES = 'analysis';
    const TYPE_SERVICES = 'services';

    const RELATION_TYPE = 'price-agreement-act';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'status',
        'date_from',
        'employee_id',
        'act_prices',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->addChatRoom();
        });
    }

    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function act_prices()
    {
        return $this->hasMany(\App\V1\Models\PriceAgreementAct\Price::class, 'price_agreement_act_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function addChatRoom()
    {
        $repository = app(EmployeeRepository::class);
        $clinics = [];
        $this->act_prices->each(function($price) use (&$clinics) {
            $clinics = array_unique(array_merge($clinics, array_column($price->clinicsToSave, 'clinic_id')));
        });
     
        $usersByRolePermission = $repository->getFilteredQuery([
            'has_role_permission' => 'price-agreement-acts-chat.access',
            'status' => Employee::STATUS_WORKING,
        ])->get();
        $usersDirectPermission = $repository->getFilteredQuery([
            'has_permission' => 'price-agreement-acts-chat.access',
            'status' => Employee::STATUS_WORKING,
        ])->get();
        $employeeOnlyClinicPermission = $repository->getFilteredQuery([
            'has_permission' => 'price-agreement-acts-chat.access-clinic',
            'status' => Employee::STATUS_WORKING,
            'clinic' => $clinics
        ])->get();
        $employeeOnlyClinicRole = $repository->getFilteredQuery([
            'has_role_permission' => 'price-agreement-acts-chat.access-clinic',
            'status' => Employee::STATUS_WORKING,
            'clinic' => $clinics
        ])->get();
    
        $users = array_merge(
            $usersDirectPermission->pluck('id')->toArray(),
            $usersByRolePermission->pluck('id')->toArray(),
            $employeeOnlyClinicPermission->pluck('id')->toArray(),
            $employeeOnlyClinicRole->pluck('id')->toArray(),
            [\Auth::user()->getEmployeeId()]
            );

        Room::create([
            'id' => $this->id,
            'employees' => array_unique($users)
        ]);


    }
}
