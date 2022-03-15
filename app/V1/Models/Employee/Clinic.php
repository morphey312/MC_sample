<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\EmployeeSiteEnquiryCategory;
use App\V1\Models\Specialization;
use App\V1\Models\Employee;
use App\V1\Models\Clinic as GenericClinic;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Exceptions\ConstraintException;
use App\V1\Contracts\Repositories\DaySheetRepository;

class Clinic extends BaseModel implements ClinicShared
{
    const STATUS_WORKING = 'working';
    const STATUS_NOT_WORKING = 'not_working';
    const STATUS_REMOVED = 'removed';
    const STATUS_PROBATION = 'probation';

    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'clinic_id',
        'status',
        'position_id',
        'is_primary',
        'can_recieve_transfer',
        'doctor',
        'sip_number',
        'sip_password',
        'specializations',
        'date_start_working',
        'date_end_working',
        'enquiry_categories'
    ];

    /**
     * @var string
     */
    protected $table = 'employee_clinics';

    /**
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'can_recieve_transfer' => 'boolean',
    ];

    /**
     * @var array
     */
    public $specializationsToSave = null;

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->specializationsToSave !== null) {
                $model->saveSpecializations($model->specializationsToSave);
            }

            if ($model->can_recieve_transfer == true) {
                $model->createEmployeeCashboxes();
            }
        });

        static::deleting(function ($model) {
            if (static::handleEmptySheets($model)) {
                throw new ConstraintException("");
                return false;
            }
        });
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(GenericClinic::class, 'clinic_id');
    }

    /**
     * Handle empty sheets before delete
     *
     * @return Boolean
     */
    public static function handleEmptySheets($model)
    {
        $repository = app(DaySheetRepository::class);
        $daysheets = $repository->all($repository->filter([
            'employees' => $model->employee_id,
            'clinic' => $model->clinic_id,
        ]));

        if ($daysheets) {
            foreach ($daysheets as $daysheet) {
                if ($daysheet->time_sheets()->exists()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Related position
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * Related specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'employee_specialization', 'employee_clinic_id', 'specialization_id')
                    ->withPivot('priority', 'workspace_id')
                    ->orderBy('priority', 'desc');
    }

    /**
     * Related enquiry categories
     *
     *
     */
    public function enquiry_categories()
    {
        return $this->hasMany(EmployeeSiteEnquiryCategory::class, 'employee_clinic_id');
    }

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'employee_clinic_id');
    }

    /**
     * Set doctor relation
     *
     * @param $value
     */
    public function setDoctorAttribute($value)
    {
        if ($value instanceof Doctor) {
            $doctor = $value;
        } elseif (is_array($value)) {
            $doctor = $this->doctor ?: new Doctor();
            $doctor->fill($value);
        } else {
            $doctor = null;
        }
        $this->getRelationsManager()->assign('doctor', $doctor);
    }

    /**
     * Get decrypted password attribute
     *
     * @return string
     */
    public function getSipPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Encrypt and set password attribute
     *
     * @param string $value
     */
    public function setSipPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['sip_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Set specializations to save
     *
     * @param mixed $value
     */
    public function setSpecializationsAttribute($value)
    {
        $this->specializationsToSave = $value;
    }

    /**
     * Save employee specializations
     *
     * @param array $data
     */
    public function saveSpecializations(array $data)
    {
        $this->specializations()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['specialization_id'],
                'data' => Arr::only($item, ['workspace_id', 'priority']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Check if employee is operator in this clinic
     *
     * @return bool
     */
    public function isOperator()
    {
        return $this->position !== null && $this->position->is_operator;
    }

    /**
     * Check if employee has specializations in this clinic
     *
     * @return bool
     */
    public function hasSpecializations()
    {
        return $this->position !== null && $this->position->has_specialization;
    }

    /**
     * Check if employee is doctor in this clinic
     *
     * @return bool
     */
    public function isDoctor()
    {
        return $this->position !== null && $this->position->is_doctor;
    }

    /**
     * Check if employee is cashier in this clinic
     *
     * @return bool
     */
    public function isCashier()
    {
        return $this->position !== null && $this->position->is_cashier;
    }

    /**
     * Check if employee has VoIP in this clinic
     *
     * @return bool
     */
    public function hasVoIP()
    {
        return $this->position !== null && $this->position->has_voip;
    }

    /**
     * Check if employee is reception in this clinic
     *
     * @return bool
     */
    public function isReception()
    {
        return $this->position !== null && $this->position->is_reception;
    }

    /**
     * Create cachboxes
     */
    public function createEmployeeCashboxes()
    {
        $this->employee->createClinicCashboxes($this->clinic);
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
