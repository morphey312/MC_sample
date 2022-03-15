<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;

class Speciality extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'speciality_id',
        'primary',
        'level',
        'qualification_type',
        'attestation_name',
        'attestation_date',
        'valid_to_date',
        'certificate_number',
    ];

    /**
     * @var string
     */
    protected $table = 'employee_specialities';

    /**
     * @var array
     */
    protected $dates = [
        'valid_to_date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'primary' => 'boolean',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->toggleOthers();
        });
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
     * Related speciality type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function speciality()
    {
        return $this->belongsTo(SpecialityType::class, 'speciality_id');
    }

    /**
     * Turn off primary flag on other specialities if this one is primary
     */
    protected function toggleOthers()
    {
        if ($this->primary && $this->isDirty('primary')) {
            $query = static::where('employee_id', '=', $this->employee_id)
                ->where('primary', '=', 1);
            if ($this->exists) {
                $query->where('id', '!=', $this->id);
            }
            $query->update([
                'primary' => 0,
            ]);
        }
    }
}
