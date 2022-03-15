<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use Illuminate\Database\Eloquent\Model;

class EmployeeSiteEnquiryCategory extends BaseModel
{
    const CATEGORY_DEFAULT = 'default';
    const CATEGORY_COVID = 'covid';
    const CATEGORY_TOMOGRAPHY = 'tomography';
    const CATEGORY_RENTGEN = 'rentgen';

    protected $table = 'employee_enquiry_categories';

    /**
     * @var array
     */
    protected $fillable = [
        'employee_clinic_id',
        'category'
    ];

    public $timestamps = false;
}
