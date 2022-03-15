<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class Medicine extends BaseModel
{
    const RELATION_TYPE = 'medicine';

   /**
     * @var array
     *
    */
    protected $fillable = [
        'medicine_uid',
        'new_medicine_uid',
        'parent_uid',
        'code',
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'description_full',
        'new_description_full',
        'measure',
        'type',
        'articul',
    ];

    /**
     * Related medicine_stores
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function store_rests()
    {
    	return $this->belongsToMany(MedicineStore::class, 'medicine_store_rests', 'medicine_id', 'store_id')
                    ->withPivot('rest', 'cost', 'self_cost', 'firm_id', 'clinic_id')
                    ->withTimestamps();
    }

    /**
     * Related medicine_firms
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function firm_rests()
    {
        return $this->belongsToMany(MedicineFirm::class, 'medicine_store_rests', 'medicine_id', 'firm_id')
            ->withPivot('rest', 'cost', 'self_cost', 'firm_id')
            ->withTimestamps();
    }

    /**
     * Get store rests by clinic_id
     *
     * @param int $clinic_id
     *
     * @return @mixed
     */
    public function getClinicStoreRests($clinic_id)
    {
        return $this->store_rests()->whereHas('clinics', function($query) use ($clinic_id) {
            $query->where('clinic_id', '=', $clinic_id);
        })->get();
    }
}
