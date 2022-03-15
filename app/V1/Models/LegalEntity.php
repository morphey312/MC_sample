<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class LegalEntity extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    /**
     * @var string
     */
    protected $table = 'legal_entities';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'short_name',
        'post_address',
        'phone_number',
    ];

    /**
     * Related clinics
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */ 
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'legal_entity_clinics', 'legal_entity_id', 'clinic_id')
            ->withPivot('agreement', 'agreement_active')
            ->orderBy('name');
    }

    /**
     * Related company clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entity_clinics()
    {
        return $this->hasMany(LegalEntity\Clinic::class, 'legal_entity_id');
    }

    /**
     * Get title attribute
     * 
     * @return bool
     */
    public function getTitleAttribute()
    {
        return (strlen($this->short_name) === 0) ? $this->name : $this->short_name;
    }
}
