<?php

namespace App\V1\Models;


class MedicineFirm extends BaseModel
{
    /**
     * @var array
     *
     */
    protected $fillable = [
        'firm_uid',
        'parent',
        'code',
        'description',
        'is_group',
    ];

    /**
     * Related medicines
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'medicine_store_rests', 'firm_id', 'medicine_id')
            ->withPivot('rest', 'cost', 'self_cost')
            ->withTimestamps();
    }
}
