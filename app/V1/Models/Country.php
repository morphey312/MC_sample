<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class Country extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'countries';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'clinics',
    ];

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'country_id');
    }
}
