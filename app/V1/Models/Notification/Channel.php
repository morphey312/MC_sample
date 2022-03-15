<?php

namespace App\V1\Models\Notification;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;

class Channel extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    const TYPE_TELEGRAM = 'telegram';
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';

    /**
     * @var string
     */
    protected $table = 'notification_channels';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'account_name',
        'account_password',
        'settings',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'templates',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'settings' => 'json',
    ];

    /**
     * Related templates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templates()
    {
        return $this->hasMany(Template::class, 'channel_id');
    }

    /**
     * Get decrypted password attribute
     *
     * @return string
     */
    public function getAccountPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            return '';
        }
    }

    /**
     * Encrypt and set password attribute
     *
     * @param string $value
     */
    public function setAccountPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['account_password'] = Crypt::encryptString($value);
        }
    }
}
