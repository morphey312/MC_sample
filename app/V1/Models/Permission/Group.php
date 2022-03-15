<?php

namespace App\V1\Models\Permission;

use App\V1\Models\BaseModel;

class Group extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name'
    ];
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * @var string
     */ 
    protected $table = 'permissions_groups';
}
