<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class Permission extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * Related group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Permission\Group::class, 'group_id');
    }
    
    /**
     * Related roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
