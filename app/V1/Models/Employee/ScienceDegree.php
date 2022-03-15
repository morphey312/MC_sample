<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Country;

class ScienceDegree extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'country_id',
        'city',
        'degree',
        'institution_name',
        'diploma_number',
        'speciality',
        'issued_date',
    ];

    /**
     * @var string
     */
    protected $table = 'employee_science_degrees';

    /**
     * @var array
     */
    protected $dates = [
        'issued_date',
    ];

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
     * Related country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
