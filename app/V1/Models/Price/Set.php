<?php

namespace App\V1\Models\Price;

use App\V1\Models\BaseModel;
use App\V1\Models\Price;
use App\V1\Models\InsuranceCompany;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class Set extends BaseModel implements SharedResourceInterface
{
    use HasConstraint, SharedResource;

    /**
     * Model table name
     */ 
    protected $table = 'price_sets';

    /**
     * @var array
     * 
     */
    protected $fillable = [
        'type',
        'owner_id',
        'owner_type',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'prices',
    ];

    /**
     * Related services
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function prices()
    {
        return $this->hasMany(Price::class, 'price_set_id');
    }

    /**
     * Related owner
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */ 
    public function owner()
    {
        return $this->morphTo();
    }
}
