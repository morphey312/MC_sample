<?php

namespace App\V1\Models\Clinic;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class Group extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    const RELATION_TYPE = 'clinic_group';

    /**
     * Specifying table name
     *
     * @var string
     */
    protected $table= 'clinic_groups';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
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
        return $this->hasMany(Clinic::class, 'group_id');
    }
}
