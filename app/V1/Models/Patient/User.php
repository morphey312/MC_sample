<?php

namespace App\V1\Models\Patient;

use App\V1\Observers\AppointmentSmsReminderObserver;
use App\V1\Sms\Patient\Messages\NewUserPasswordMessage;
use App\V1\Sms\Patient\Messages\PasswordResetMessage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\V1\Models\Patient;
use App\V1\Repositories\Query\Builder\EloquentBuilder;
use App\V1\Repositories\Query\Builder\QueryBuilder;
use Messenger;
use App\V1\Traits\Models\HasReverseRelation;

class User extends Authenticatable
{
    use HasReverseRelation;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patient_users';

    /**
     * @var array
     */
    protected $fillable = [
        'phone',
        'password',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     *
     * @return EloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return QueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->clearRelations();
        });

        static::updating(function ($model) {
            $model->changeRelations();
        });
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
        }
    }

    /**
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related registration request
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function request()
    {
        return $this->hasOne(Registration::class, 'user_id');
    }

    /**
     * Get user first name
     *
     * @return string
     */
    public function getFirstnameAttribute()
    {
        return $this->patient_id
            ? $this->patient->firstname
            : $this->request->firstname;
    }

    /**
     * Get user middle name
     *
     * @return string
     */
    public function getMiddlenameAttribute()
    {
        return $this->patient_id
            ? $this->patient->middlename
            : $this->request->middlename;
    }

    /**
     * Get user last name
     *
     * @return string
     */
    public function getLastnameAttribute()
    {
        return $this->patient_id
            ? $this->patient->lastname
            : $this->request->lastname;
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->patient_id
            ? ($this->patient->email ? $this->patient->email->value : null)
            : $this->request->email;
    }

    /**
     * Get user card number
     *
     * @return string
     */
    public function getCardNumberAttribute()
    {
        return $this->patient_id && $this->patient !== null
            ? object_get($this->patient->cards->first(), 'number')
            : null;
    }

    /**
     * Get user clinic IDs
     *
     * @return array
     */
    public function getClinicsAttribute()
    {
        return $this->patient_id && $this->patient !== null
            ? $this->patient->clinics->pluck('id')
            : [];
    }

    /**
     * Get user primary clinic ID
     *
     * @return int
     */
    public function getPrimaryClinicIdAttribute()
    {
        if ($this->patient_id && $this->patient !== null) {
            $contacts = $this->patient->contact_details;
            if (isset($contacts['primary_phone_clinic'])) {
                return $contacts['primary_phone_clinic'];
            }
        }

        return null;
    }

    /**
     * Get is user confirmed
     *
     * @return bool
     */
    public function getConfirmedAttribute()
    {
        return $this->patient_id
            ? $this->patient->is_confirmed
            : false;
    }

    /**
     * Get user's relatives
     *
     * @return bool
     */
    public function getRelativesAttribute()
    {
        return $this->patient_id
            ? $this->patient->relatives
            : [];
    }

    /**
     * Break relation to current patient
     */
    protected function clearRelations()
    {
        if ($this->request !== null) {
            $this->request->delete();
        }
        if ($this->patient !== null) {
            $this->patient->has_registration = false;
            $this->patient->save();
        }
    }

    /**
     * Change relation to another patient
     */
    protected function changeRelations()
    {
        if ($this->isDirty('patient_id')) {
            $old = $this->fresh();
            if ($old->patient !== null) {
                $old->patient->has_registration = false;
                $old->patient->save();
            }
            if ($this->patient !== null) {
                $this->patient->has_registration = true;
                $contacts = $this->patient->contact_details;
                if ($contacts['primary_phone_number'] !== $this->phone) {
                    if (!empty($contacts['secondary_phone_number'])) {
                        $this->patient->comment .= (' ' . $contacts['secondary_phone_number']);
                        if (!empty($contacts['secondary_phone_comment'])) {
                            $this->patient->comment .= (' (' . $contacts['secondary_phone_comment'] . ')');
                        }
                    }
                    $contacts['secondary_phone_number'] = $contacts['primary_phone_number'];
                    $contacts['secondary_phone_clinic'] = $contacts['primary_phone_clinic'];
                    $contacts['secondary_phone_comment'] = $contacts['primary_phone_comment'];
                    $contacts['primary_phone_number'] = $this->phone;
                    $contacts['primary_phone_comment'] = 'Personal cabinet';
                    $this->patient->contact_details = $contacts;
                }
                $this->patient->save();
            }
        }
    }

    /**
     * Send password to a new user
     * 
     * @param string $password
     */
    public function sendPassword($password)
    {
        Messenger::send(new NewUserPasswordMessage($this, $password));
    }

    /**
     * Send new password to a user
     * 
     * @param string $password
     */
    public function sendNewPassword($password)
    {
        Messenger::send(new PasswordResetMessage($this, $password));
    }
}
