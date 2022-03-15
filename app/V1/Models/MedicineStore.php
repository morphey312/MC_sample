<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class MedicineStore extends BaseModel
{
    /**
     * @var array
     *
    */
    protected $fillable = [
        'store_uid',
        'code',
        'description',
    ];

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_medicine_store', 'medicine_store_id', 'clinic_id');
    }

    /**
     * Related medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'medicine_store_rests', 'store_id', 'medicine_id')
                    ->withPivot('rest', 'cost', 'self_cost', 'firm_id')
                    ->withTimestamps();
    }

    /**
     * Get store clinic by clinic id
     *
     * @param int $clinic_id
     *
     * @return mixed
     */
    public function getClinic($clinic_id)
    {
        return $this->clinics()->where('id', '=', $clinic_id)->first();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }

    /**
     * @param int $firmId
     */
    public function getFirmDescription(int $firmId): string
    {
        $firm = MedicineFirm::find($firmId);

        return $firm ? $firm->description : '';
    }
}
