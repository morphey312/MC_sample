<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee\OutclinicDiagnostic;
use App\V1\Models\Employee\OutclinicSpecialization;
use App\V1\Models\User;
use App\V1\Models\Appointment\Limitation;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Models\DaySheetOwner;
use App\V1\Traits\Models\AppointmentDoctor;
use App\V1\Traits\Models\CallRequestDoctor;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Employee\OutclinicMedicine;
use App\V1\Models\Patient\InformationSource;
use App\V1\Contracts\Repositories\EmployeeRepository;
use Illuminate\Notifications\Notifiable;

class Employee extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;
    use HasConstraint;
    use DaySheetOwner;
    use AppointmentDoctor;
    use CallRequestDoctor;
    use Notifiable;

    const OPERATOR = 'operator';
    const DOCTOR = 'doctor';
    const CASHIER = 'cashier';
    const SURGERY = 'surgery';
    const STATUS_WORKING = 'working';

    const RELATION_TYPE = 'employees';

    const SYSTEM_STATUS_ANALYSES = 'for_analyses';
    const SYSTEM_STATUS_ONLINE_PAYMENT = 'for_online_payment';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    /**
     * @var array
     */
    protected $fillable = [
        'phone',
        'additional_phone',
        'email',
        'birth_date',
        'gender',
        'tax_id',
        'no_tax_id',
        'experience',
        'about',
        'last_name',
        'first_name',
        'middle_name',
        'is_translator',
        'system_status',
        'user',
        'preferred_language',
        'copy_to_portal',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'sent_to_ehealth_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'telegram' => 'boolean',
        'is_translator' => 'boolean',
        'no_tax_id' => 'boolean',
        'active_in_ehealth' => 'boolean',
        'copy_to_portal' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'treatment_courses',
        'issued_personal_tasks',
        'personal_tasks',
        'operator_appointments',
        'call_process_logs',
        'calls',
        'session_logs',
        'day_sheets',
        'call_requests',
        'doctor_appointments',
        'employee_clinics',
        'employee_documents',
        'employee_educations',
        'employee_qualifications',
        'employee_specialities',
        'employee_science_degrees',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->user) {
                $model->user->company_id = $model->company_id;
            }
        });
    }

    /**
     * Related user
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'employee_clinics', 'employee_id', 'clinic_id');
    }

    /**
     * Related employee clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_clinics()
    {
        return $this->hasMany(Employee\Clinic::class, 'employee_id')
            ->orderBy('is_primary', 'desc');
    }

    /**
     * Related primary employee clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primary_employee_clinic()
    {
        return $this->hasOne(Employee\Clinic::class, 'employee_id')
            ->where('is_primary', '=', 1);
    }

    /**
     * Related employee documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_documents()
    {
        return $this->hasMany(Employee\Document::class, 'employee_id');
    }

    /**
     * Related employee educations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_educations()
    {
        return $this->hasMany(Employee\Education::class, 'employee_id');
    }

    /**
     * Related employee qualifications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_qualifications()
    {
        return $this->hasMany(Employee\Qualification::class, 'employee_id');
    }

    /**
     * Related employee specialities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_specialities()
    {
        return $this->hasMany(Employee\Speciality::class, 'employee_id');
    }

    /**
     * Related employee science degrees
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employee_science_degrees()
    {
        return $this->hasMany(Employee\ScienceDegree::class, 'employee_id');
    }

    /**
     * Get specializations by clinic
     *
     * @param int $clinic
     *
     * @return collection
     */
    public function clinicSpecializations($clinic)
    {
        return object_get($this->getClinicById($clinic), 'specializations', collect([]));
    }

    /**
     * Get enquiry_categories by clinic
     *
     * @param int $clinic
     *
     * @return collection
     */
    public function clinicEnquiryCategories($clinic)
    {
        return object_get($this->getClinicById($clinic), 'enquiry_categories', collect([]));
    }

    /**
     * Get employee clinic by id
     *
     * @param int $clinicId
     *
     * @return Employee\Clinic
     */
    public function getClinicById($clinicId)
    {
        return $this->employee_clinics
            ->where('clinic_id', '=', $clinicId)
            ->first();
    }

    /**
     * Related treatment cources
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function treatment_courses()
    {
        return $this->hasMany(TreatmentCourse::class, 'doctor_id');
    }

    /**
     * Related tasks issued by this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issued_personal_tasks()
    {
        return $this->hasMany(PersonalTask::class, 'employee_id');
    }

    /**
     * Related tasks allocated to this employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personal_tasks()
    {
        return $this->hasMany(PersonalTask::class, 'operator_id');
    }

    /**
     * Related session logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function session_logs()
    {
        return $this->hasMany(SessionLog::class, 'employee_id');
    }

    /**
     * Related call process logs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_process_logs()
    {
        return $this->hasMany(Call\ProcessLog::class, 'operator_id');
    }

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'employee_id');
    }

    /**
     * Related day operator appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function operator_appointments()
    {
        return $this->hasMany(Appointment::class, 'operator_id');
    }

    /**
     * Related appointment limitations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function appointment_limitations()
    {
        return $this->belongsToMany(Limitation::class, 'appointment_limitation_doctor', 'doctor_id', 'appointment_limitation_id')
                    ->withPivot('limitation_percent', 'is_hard_limit');
    }

    /**
     * Set user relation on create
     *
     * @param $value
     */
    public function setUserAttribute($value)
    {
        if ($value instanceof User) {
            $user = $value;
        } else {
            $user = $this->user ?: new User();
            if (is_array($value)) {
                $user->fill($value);
            }
        }
        $this->getRelationsManager()->assign('user', $user);
    }

    /**
     * Get employee positions across all clinics
     *
     * @return array
     */
    public function getPositionsAttribute()
    {
        return $this->employee_clinics->pluck('position', 'position_id');
    }

    /**
     * Get operator sip account
     *
     * @return array
     */
    public function getSipAccounts()
    {
        $accounts = [];
        foreach ($this->employee_clinics as $clinic) {
            if ($clinic->sip_number != null && $clinic->sip_password != null) {
                $accounts[$clinic->clinic_id] = [
                    'sip_number' => $clinic->sip_number,
                    'sip_password' => $clinic->sip_password,
                ];
            }
        }
        return $accounts;
    }

    /**
     * Get combined name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]));
    }

    /**
     * Get last name with initials
     *
     * @return string
     */
    public function getEmployeeInitialsAttribute()
    {
        $middleName = $this->middle_name ? mb_substr($this->middle_name, 0, 1).'.' : '';

        return $this->last_name.' '.mb_substr($this->first_name, 0, 1).'.'.$middleName;
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->employee_clinics->pluck('clinic_id')->all();
    }

    /**
     * Check if employee belongs to particular clinic
     *
     * @param int $clinicId
     *
     * @return bool
     */
    public function belongsToClinic($clinicId)
    {
        return in_array($clinicId, $this->getClinicIds());
    }

    /**
     * Check if employee is operator
     *
     * @return bool
     */
    public function isOperator()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->isOperator();
        })->count() !== 0;
    }

    /**
     * Check if employee is doctor
     *
     * @return bool
     */
    public function isDoctor()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->isDoctor();
        })->count() !== 0;
    }

    /**
     * Check if employee is cashier
     *
     * @return bool
     */
    public function isCashier()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->isCashier();
        })->count() !== 0;
    }

    /**
     * Check if employee is reception
     *
     * @return bool
     */
    public function isReception()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->isReception();
        })->count() !== 0;
    }

    /**
     * Check if employee has access to VoIP
     *
     * @return bool
     */
    public function hasVoIP()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->hasVoIP();
        })->count() !== 0;
    }

    /**
     * Check if employee can recieve transfer
     *
     * @return bool
     */
    public function canRecieveTransfer()
    {
        return $this->employee_clinics->filter(function($clinic) {
            return $clinic->can_recieve_transfer == 1;
        })->count() !== 0;
    }

    /**
     * Get actual appointment limitation by clinic and date
     *
     * @param $date
     * @param int $clinic_id
     *
     * @return array
     */
    public function actualAppointmentLimitation($date, $clinic_id)
    {
        return $this->appointment_limitations()
            ->where([
                ['date_from', '<=', $date],
                ['date_to', '>=', $date],
                ['clinic_id', '=', $clinic_id],
            ])->get();
    }

    /**
     * Related featured analyses
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function featured_analyses()
    {
        return $this->belongsToMany(Analysis::class, 'doctor_featured_analysis', 'doctor_id', 'analysis_id');
    }

    /**
     * Get active related featured analyses
     * @param null $filter
     * @param null $sorter
     * @return mixed
     */

    public function getActiveFeaturedAnalyses($filter = null, $sorter = null)
    {
        $query = $this->featured_analyses();
        $query->where($query->qualifyColumn('disabled'), '=', 0);

        if ($filter != null) {
            $filter->apply($query);
        }
        if ($sorter != null) {
            $sorter->apply($query);
        }
        return $query;
    }

    /**
     * Related featured medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function featured_medicines()
    {
        return $this->belongsToMany(Medicine::class, 'doctor_featured_medicine', 'doctor_id', 'medicine_id');
    }

    /**
     * Related featured services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function featured_services()
    {
        return $this->belongsToMany(Service::class, 'doctor_featured_service', 'doctor_id', 'service_id');
    }

    /**
     * Related featured specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function featured_specializations()
    {
        return $this->belongsToMany(Specialization::class, 'doctor_featured_specialization', 'doctor_id', 'specialization_id');
    }

    /**
     * Get featured services by specialization_group, hasPrice
     *
     * @return mixed
     */
    public function getFeaturedFilteredServices($filter = null)
    {
        $query = $this->featured_services()->where('disabled', '=', 0);

        if ($filter != null) {
            $filter->apply($query);
        }
        return $query;
    }

    /**
     * Related cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function cashboxes()
    {
        return $this->hasMany(Employee\Cashbox::class, 'cashier_id');
    }

    /**
     * Related active cashboxes
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function active_cashboxes()
    {
        return $this->cashboxes()
            ->whereHas('payment_method', function($query) {
                $query->where('is_enabled', '=', 1);
            });
    }

    /**
     * Create employee cashboxes
     *
     * @param Clinic $clinic
     */
    public function createClinicCashboxes($clinic)
    {
        $paymentMethods = $clinic->non_online_payment_methods->map(function($method) use ($clinic) {
            return [
                'payment_method_id' => $method->id,
                'clinic_id' => $clinic->id,
            ];
        });

        $existing = $this->getClinicCashboxes($clinic->id)
            ->pluck('payment_method_id')
            ->all();

        $new = $paymentMethods->filter(function($method) use ($existing) {
            return !in_array($method['payment_method_id'], $existing);
        })->all();

        if(!empty($new)) {
            $this->cashboxes()->createMany($new);
        }
    }

    /**
     * Get cashboxes by clinic_id
     *
     * @param int $clinic
     *
     * @return @mixed
     */
    public function getClinicCashboxes($clinic_id)
    {
        return $this->active_cashboxes->where('clinic_id', '=', $clinic_id);
    }

    /**
     * Related outclinic medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function outclinic_medicines()
    {
        return $this->hasMany(OutclinicMedicine::class, 'doctor_id');
    }

    /**
     * Related active outclinic medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function active_outclinic_medicines()
    {
        return $this->outclinic_medicines()->where('is_deleted', '=', 0);
    }

    /**
     * Related outclinic specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function outclinic_specializations()
    {
        return $this->hasMany(OutclinicSpecialization::class, 'doctor_id');
    }

    /**
     * Related outclinic diagnostics
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function outclinic_diagnostics()
    {
        return $this->hasMany(OutclinicDiagnostic::class, 'doctor_id');
    }

    /**
     * Related active outclinic specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function active_outclinic_specializations()
    {
        return $this->outclinic_specializations()->where('is_deleted', '=', 0);
    }

    /**
     * Related active outclinic diagnostics
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function active_outclinic_diagnostics()
    {
        return $this->outclinic_diagnostics()->where('is_deleted', '=', 0);
    }

    /**
     * Related outclinic medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function cashier_session_logs()
    {
        return $this->hasMany(CashierSessionLog::class, 'employee_id');
    }

    /**
     * Get opened cashier session log
     *
     * @return @mixed
     */
    public function getActiveCashierSession()
    {
        return $this->cashier_session_logs()->whereNull('end')->first();
    }

    /**
     * Related information_source
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function information_source()
    {
        return $this->hasOne(InformationSource::class, 'employee_id');
    }

    /**
     * Related operator bonus
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operator_bonus()
    {
        return $this->hasOne(Employee\OperatorBonus::class, 'operator_id');
    }

    /**
     * Get session log wrapups average duration in period
     *
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return mixed
     */
    public function getWrapupsAverage($dateFrom , $dateTo)
    {
        return $this->session_logs()
            ->where('action', '=', SessionLog::ACTION_WRAPUP_END)
            ->where('created_at', '>=', $dateFrom . ' 00:00:00')
            ->where('created_at', '<=', $dateTo . ' 23:59:59')
            ->avg('duration');
    }

    /**
     * Get employee with cashbox for online payment
     */
    public static function getOnlinePaymentCashier()
    {
        $repository = app(EmployeeRepository::class);
        return $repository->getOnlinePaymentCashier();
    }


    /**
     * Get online cashier data
     *
     * @return array
     */
    public static function getOnlineCashierData()
    {
        $onlineCashier = static::getOnlinePaymentCashier();
        if (!$onlineCashier) {
            return null;
        }
        $serviceCashbox = $onlineCashier->cashboxes->first(function($cashbox) {
            return $cashbox->payment_method != null &&
                   $cashbox->payment_method->online_payment == 1 &&
                   $cashbox->payment_method->use_cash == 0;
        });

        return [
            'cashier_id' => $onlineCashier->id,
            'service_cashbox_id' => $serviceCashbox ? $serviceCashbox->id : null,
        ];
    }

    /**
     * Get employee with system_status for analyses
     *
     * @return mixed
     */
    public static function forAnalyses()
    {
        $repository = app(EmployeeRepository::class);
        return $repository->findForAnalyses();
    }

    /**
     * Verify employee has working or probation status in any clinic
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->employee_clinics()
            ->whereIn('status', [Employee\Clinic::STATUS_WORKING, Employee\Clinic::STATUS_PROBATION])
            ->count() != 0;
    }

    /**
     * Related msp
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function msp()
    {
        return $this->belongsTo(Msp::class, 'msp_id');
    }

    /**
     * Related service types
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function service_types()
    {
        return $this->hasMany(Employee\ServiceType::class, 'employee_id');
    }

    /**
     * Related Chat rooms
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function chat_rooms()
    {
        return $this->belongsToMany(Chat\Room::class, 'chat_room_users', 'employee_id', 'room_id');
    }

    /**
     * @param $dbPayments
     * @param $paymentMethod
     * @return bool
     */
    private function isNewPaymentMethod($dbPayments, $paymentMethod): bool
    {
        return $dbPayments->filter(function ($item) use ($paymentMethod) {
                return $item->payment_method_id === $paymentMethod['payment_method_id'];
            })->count() === 0;
    }
}
