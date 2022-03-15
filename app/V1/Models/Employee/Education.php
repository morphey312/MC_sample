<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Country;

class Education extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'country_id',
        'city',
        'institution_name',
        'issued_date',
        'diploma_number',
        'degree',
        'speciality',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'employee_educations';

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
