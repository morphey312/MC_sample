<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;

class Qualification extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'type',
        'institution_name',
        'speciality',
        'issued_date',
        'certificate_number',
        'valid_to',
        'additional_info',
    ];

    /**
     * @var string
     */
    protected $table = 'employee_qualifications';

    /**
     * @var array
     */
    protected $dates = [
        'issued_date',
        'valid_to',
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
}
