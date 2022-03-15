<?php

namespace App\V1\Models\Patient;

use App\V1\Contracts\Services\Permissions\ResourceHolder;
use App\V1\Models\BaseModel;
use App\V1\Models\Employee;
use App\V1\Models\Patient\IssuedMedicine\Document;
use App\V1\Models\User;
use Illuminate\Support\Facades\Auth;

class IssuedMedicine extends BaseModel
{

    const RELATION_TYPE = 'issued_medicine';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'issued_medicines';

    /**
     * @var array
     */
    protected $fillable = [
        'assigned_medicine_id',
        'quantity',
        'medicine_document_id',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user instanceof ResourceHolder) {
                $model->issued_by_id = $user->getEmployeeId();
            }
        });
    }

    /**
     * Related assigned_medicine
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigned_medicine()
    {
        return $this->belongsTo(AssignedMedicine::class, 'assigned_medicine_id');
    }

    /**
     * Related document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicine_document()
    {
        return $this->belongsTo(Document::class, 'medicine_document_id');
    }

    /**
     * Issued
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(Employee::class, 'issued_by_id');
    }
}
