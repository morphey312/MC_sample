<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Models\Employee;

class Document extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'type',
        'number',
        'issued_at',
        'issued_by',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'employee_documents';

    /**
     * @var array
     */
    protected $dates = [
        'issued_at',
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
