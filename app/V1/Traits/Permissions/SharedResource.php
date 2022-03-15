<?php

namespace App\V1\Traits\Permissions;

use App\V1\Contracts\Services\Permissions\ResourceHolder;
use App\V1\Models\User;
use App\V1\Models\Company;
use Auth;

trait SharedResource
{
    /**
     * If true, resource can be accessible by owner but not company
     * 
     * @var bool
     */ 
    protected $private = false;
    
    /**
     * @inherit
     */ 
    public function getUserId()
    {
        return $this->created_by_id;
    }
    
    /**
     * @inherit
     */ 
    public function getCompanyId()
    {
        return $this->company_id;
    }
    
    /**
     * @inherit
     */ 
    public function isOwnedBy(ResourceHolder $user)
    {
        if ($this->private) {
            return $this->getUserId() === $user->getUserId();
        }
        return $this->getCompanyId() === $user->getCompanyId();
    }
    
    /**
     * @inherit
     */ 
    public function isAccessibleBy(ResourceHolder $user)
    {
        if ($this->private) {
            return $this->getUserId() === $user->getUserId();
        }
        return $this->getCompanyId() === null 
            || $this->getCompanyId() === $user->getCompanyId();
    }
    
    /**
     * @inherit
     */
    public function applyAccessFilter($builder, ResourceHolder $user)
    {
        if ($this->private) {
            $builder->where($builder->qualifyColumn('created_by_id'), '=', $user->getUserId());
        } else {
            $builder->where(function($builder) use($user) {
                $builder->whereNull($builder->qualifyColumn('company_id'))
                    ->orWhere($builder->qualifyColumn('company_id'), '=', $user->getCompanyId());
            });
        }
    }
    
    /**
     * Boot this trait
     */ 
    public static function bootSharedResource()
    {
        static::creating(function ($model) {
            $user = Auth::user();
            if ($user instanceof ResourceHolder) {
                $model->created_by_id = $user->getUserId();
                $model->company_id = $user->getCompanyId();
            }
        });
    }
    
    /**
     * Related user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function created_by()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Related company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}