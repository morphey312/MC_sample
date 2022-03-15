<?php

namespace App\V1\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\V1\Contracts\Services\Permissions\ResourceHolder;
use App\V1\Contracts\Services\Permissions\RoleHolder;
use App\V1\Contracts\Services\Permissions\PermissionsHolder;
use App\V1\Contracts\Services\Permissions\SharedResource;
use App\V1\Contracts\Multilingual;
use Masterfri\SmartRelations\SmartRelations;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App;
use Illuminate\Broadcasting\PrivateChannel;

class User extends Authenticatable implements JWTSubject, RoleHolder, PermissionsHolder,
    ResourceHolder, ClinicShared, Multilingual
{
    use SmartRelations, Notifiable;

    const TYPE_EMPLOYEE = Employee::RELATION_TYPE;
    const TYPE_PATIENT = Patient::RELATION_TYPE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login',
        'password',
        'roles',
        'permissions',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Related roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Related permissions
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    /**
     * Related morph
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function userable()
    {
        return $this->morphTo();
    }

    /**
     * Related ehealth user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ehealth_user()
    {
        return $this->hasOne(Ehealth\User::class, 'user_id');
    }

    /**
     * Related company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Update password attribute
     *
     * @param string $value
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
            $this->attributes['password_recovery'] = Crypt::encryptString($value);
        }
    }

    /**
     * Get password recovery attribute
     *
     * @param string $value
     */
    public function getPasswordRecoveryAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * @inherit
     */
    public function getUserId()
    {
        return $this->id;
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
    public function isOwnerOf(SharedResource $resource)
    {
        return $resource->isOwnedBy($this);
    }

    /**
     * @inherit
     */
    public function canAccess(SharedResource $resource)
    {
        return $resource->isAccessibleBy($this);
    }

    /**
     * @inherit
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inherit
     */
    public function getPermissions()
    {
        return $this->permissions->pluck('name')->all();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        if ($this->userable instanceof ClinicShared) {
            return $this->userable->getClinicIds();
        }
        return [];
    }

    /**
     * Check if current user has employment
     *
     * @return bool
     */
    public function isEmployee()
    {
        return $this->userable instanceof Employee;
    }

    /**
     * Get employee model, if this user is an employee
     *
     * @return Employee
     */
    public function getEmployeeModel()
    {
        return $this->isEmployee() ? $this->userable : null;
    }

    /**
     * Verify employee has working or probation status in any clinic
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->userable->isWorking();
    }

    /**
     * Get employee ID, if this user is an employee
     *
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->isEmployee() ? $this->userable->id : 0;
    }

    /**
     * Check if user is employee of particular clinic
     *
     * @param int $clinicId
     *
     * @return bool
     */
    public function isEmployeeOf($clinicId)
    {
        if ($this->isEmployee()) {
            return $this->userable->belongsToClinic($clinicId);
        }
        return false;
    }

    /**
     * Check if user is operator
     *
     * @return bool
     */
    public function isOperator()
    {
        return $this->isEmployee()
            && $this->getEmployeeModel()->isOperator();
    }

    /**
     * Check if user is doctor
     *
     * @return bool
     */
    public function isDoctor()
    {
        return $this->isEmployee()
            && $this->getEmployeeModel()->isDoctor();
    }

    /**
     * Check if user is doctor
     *
     * @return bool
     */
    public function isCashier()
    {
        return $this->isEmployee()
            && $this->getEmployeeModel()->isCashier();
    }

    /**
     * Check if user is reception
     *
     * @return bool
     */
    public function isReception()
    {
        return $this->isEmployee()
            && $this->getEmployeeModel()->isReception();
    }

    /**
     * Check if user has access to VoIP
     *
     * @return bool
     */
    public function hasVoIP()
    {
        return $this->isEmployee()
            && $this->getEmployeeModel()->hasVoIP();
    }

    /**
     * @inherit
     */
    public function getLocaleSuffix()
    {
        $locale = App::getLocale();
        $known = $this->getKnownLocales();

        return empty($known[$locale]) ? null : $known[$locale]['suffix'];
    }


    /**
     * @inherit
     */
    public function getKnownLocales()
    {
        return config('app.locale_config', []);
    }
    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.'.$this->id;
    }
}
