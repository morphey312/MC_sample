<?php

namespace App\V1\Models\TreatmentCourse\Document;

use App\V1\Models\BaseModel;
use App\V1\Models\FileAttachment;
use App\V1\Models\Employee;
use Auth;

class Signature extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'treatment_course_document_signatures';

    /**
     * @var array
     */
    protected $fillable = [
        'file_id',
        'document_id',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->employee = Auth::user()->getEmployeeModel();
        });
    }

    /**
     * Related image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(FileAttachment::class, 'file_id');
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
}
