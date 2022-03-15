<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;
use App\V1\Models\Specialization;
use App\V1\Models\Clinic;
use App\V1\Models\Employee;
use App\V1\Models\Appointment;
use Illuminate\Support\Arr;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use Cache;

class Limitation extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    const CACHE_PREFIX = 'limitation_appointments_';
    const CACHE_TIME = 10800; // 3 hrs
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_limitations';

    /**
     * @var array
     */
    protected $fillable = [
        'clinic_id',
        'specialization_id',
        'date_from',
        'date_to',
        'limitation',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($model) {
            if ($model->limitation_doctors->count() > 0) {
                $model->limitation_doctors()->detach();
            }
        });
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
     * Related clinic
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * Related doctors
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */ 
    public function limitation_doctors()
    {
        return $this->belongsToMany(Employee::class, 'appointment_limitation_doctor', 'appointment_limitation_id', 'doctor_id')
                    ->withPivot('limitation_percent', 'is_hard_limit')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->orderBy('middle_name');
    }

    /**
     * Sync related doctors
     * 
     * @param array $data
     */ 
    public function syncDoctors($data)
    {
        $doctors = [];

        if(!empty($data)) {
            foreach($data as $row) {
                $doctors[$row['doctor_id']] = [
                    'limitation_percent' => Arr::get($row, 'limitation_percent'),
                    'is_hard_limit' => Arr::get($row, 'is_hard_limit'),
                ];
            }
        }

        $this->limitation_doctors()->sync($doctors);
    }

    /**
     * Get doctors attribute
     * 
     * @return array
     */ 
    public function getDoctorsAttribute()
    {
        $list = [];
        $doctors = $this->limitation_doctors;

        if(!empty($doctors)) {
            foreach($doctors as $doctor) {
                $clinic = $doctor->getClinicById($this->clinic_id);

                $list[] = [
                    'doctor_id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'limitation_percent' => $doctor->pivot->limitation_percent,
                    'is_hard_limit' => boolval($doctor->pivot->is_hard_limit),
                    'status' => $clinic ? $clinic->status : null,
                ];
            }
        }

        return $list;
    }

    /**
     * Count is_first appointments for this limitation by doctor
     * 
     * @return array
     */ 
    public function countAppointmentsIsFirstByDoctor()
    {
        return Cache::remember(self::CACHE_PREFIX . $this->id, self::CACHE_TIME, function () {
            return Appointment::useIndex([
                'appointments_date_index', 
                'appointments_clinic_id_foreign',
            ])
            ->where([
                ['specialization_id', '=', $this->specialization_id],
                ['is_first', '=', 1],
                ['is_deleted', '=', 0],
                ['date', '>=', $this->date_from],
                ['date', '<=', $this->date_to],
                ['clinic_id', '=', $this->clinic_id],
                ['doctor_type', '=', Employee::RELATION_TYPE],
            ])
            ->whereNotIn('appointment_status_id', function($query) {
                $query->select('id')
                    ->from('appointment_statuses')
                    ->whereIn('system_status', [
                        Appointment::STATUS_DELETED,
                        Appointment::STATUS_DIDNT_COME,
                    ]);
            })
            ->groupBy('doctor_id')
            ->toBase()
            ->selectRaw('doctor_id, count(id) as cnt')
            ->pluck('cnt', 'doctor_id');
        });
    }

    /**
     * Count is_first appointments for this limitation in total
     * 
     * @return int
     */ 
    public function countAppointmentsIsFirst()
    {
        return $this->countAppointmentsIsFirstByDoctor()->sum();
    }

    /**
     * Count is_first doctor appointments for this limitation
     * 
     * @param int $employeeId
     * 
     * @return int
     */ 
    public function countDoctorAppointmentsIsFirst($employeeId)
    {
        return $this->countAppointmentsIsFirstByDoctor()->get($employeeId, 0);
    }
    
    /**
     * @inherit
     */ 
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }
}
