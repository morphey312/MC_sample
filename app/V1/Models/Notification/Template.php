<?php

namespace App\V1\Models\Notification;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Clinic;
use App\V1\Models\Notification\Setting\Clinic as SettingClinic;
use App\V1\Models\Employee\Position;
use App\V1\Models\Notification\Setting\VoipQueue;
use Illuminate\Support\Facades\Log;

class Template extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const SCENARIO_ANALYSIS_RESULT = 'analysis_result';
    const SCENARIO_SECURE_ANALYSIS_RESULT = 'secure_analysis_result';
    const SCENARIO_TELEGRAM_CHAT_BOT_NOTIFICATION = 'telegram_chat_bot_notification';
    const SCENARIO_APPOINTMENT_SMS_REMINDER_INSTANT = 'sms_appointment_reminder_instant';
    const SCENARIO_SMS_APPOINTMENT_REMINDER_72H = 'sms_appointment_reminder_72h';
    const SCENARIO_SMS_APPOINTMENT_REMINDER_OPERATOR_MANUAL = 'sms_appointment_reminder_operator_manual';
    const SCENARIO_SMS_GENERATED_PASSWORD_FOR_PATIENT = 'sms_generated_password_for_patient';
    const SCENARIO_SMS_NEW_PASSWORD_FOR_PATIENT = 'sms_new_password_for_patient';
    const SCENARIO_SMS_AMBULANCE_CALL = 'sms_ambulance_call';
    const SCENARIO_SMS_MISSED_CALL = 'sms_missed_call';

    /**
     * @var string
     */
    protected $table = 'notification_templates';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'scenario',
        'channel_id',
        'parent_id',
        'header',
        'time',
        'inherit_header',
        'body',
        'inherit_body',
        'positions',
        'specialization_id',
        'service_id',
        'footer',
        'inherit_footer',
        'disabled',
        'clinics',
        'voip_queue',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'inherit_header' => 'boolean',
        'inherit_body' => 'boolean',
        'inherit_footer' => 'boolean',
        'disabled' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $allowedTags = [
        'p', 'div', 'br', 'b', 'i', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'pre', 'ul', 'ol', 'li', 'img', 'table', 'tbody', 'tr', 'td',
    ];

    /**
     * @var array
     */
    protected $allowedStyles = [
        'text-align',
    ];

    /**
     * Related positions
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'notification_template_positions', 'template_id', 'position_id');
    }

    /**
     * Related settings clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings_clinics()
    {
        return $this->hasMany(SettingClinic::class, 'notification_template_id');

    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'notification_template_clinics', 'template_id', 'clinic_id');
    }

    /**
     * Related channel
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    /**
     * Related settings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function setting_clinics()
    {
        return $this->hasMany(\App\V1\Models\Notification\Setting\Clinic::class, 'notification_template_id');
    }

    /**
     * Related parent template
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * Related voip queues
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function voip_queue()
    {
        return $this->hasMany(VoipQueue::class, 'notification_template_id');
    }
    /**
     * Set header
     *
     * @patam string $value
     */
    public function setHeaderAttribute($value)
    {
        $this->attributes['header'] = $this->sanitizeText($value);
    }

    /**
     * Set body
     *
     * @patam string $value
     */
    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = $this->sanitizeText($value);
    }

    /**
     * Set footer
     *
     * @patam string $value
     */
    public function setFooterAttribute($value)
    {
        $this->attributes['footer'] = $this->sanitizeText($value);
    }

    /**
     * Compose message body
     *
     * @param array $data
     *
     * @return string
     */
    public function compose($data)
    {
        return [
            'header' => $this->process($this->getHeader(), $data),
            'body' => $this->process($this->getBody(), $data),
            'footer' => $this->process($this->getFooter(), $data),
        ];
    }

    /**
     * Get template header
     *
     * @return string
     */
    protected function getHeader()
    {
        return $this->inherit_header
            ? ($this->parent === null ? '' : (string) $this->parent->header)
            : (string) $this->header;
    }

    /**
     * Get template body
     *
     * @return string
     */
    protected function getBody()
    {
        return $this->inherit_body
            ? ($this->parent === null ? '' : (string) $this->parent->body)
            : (string) $this->body;
    }

    /**
     * Get template footer
     *
     * @return string
     */
    protected function getFooter()
    {
        return $this->inherit_footer
            ? ($this->parent === null ? '' : (string) $this->parent->footer)
            : (string) $this->footer;
    }

    /**
     * Preprocess text
     *
     * @param string $text
     * @param array $vars
     *
     * @return string
     */
    protected function process($text, $vars)
    {
        return preg_replace_callback('/\{([^\}]+)\}/', function ($match) use ($vars) {
            $key = trim($match[1]);
            $default = '';
            if (strpos($key, '|') !== false) {
                list($key, $default) = explode('|', $key, 2);
            }

            $decodedKey = htmlspecialchars_decode($key);
            if (strpos($decodedKey, '?') !== false) {
                list($var, $innerKey) = explode('?', $decodedKey, 2);

                if (array_key_exists($var, $vars)) {
                    $decodedInnerKey = htmlspecialchars_decode($innerKey);
                    list($ifTrueVal, $ifFalseVal) = explode('&', $decodedInnerKey, 2);

                    return $vars[$var] ? $ifTrueVal : $ifFalseVal;
                }
            }

            return array_key_exists($key, $vars) ? $vars[$key] : $default;
        }, $text);
    }

    /**
     * Remove unwanted formatting
     *
     * @param string $text
     *
     * @return string
     */
    protected function sanitizeText($text)
    {
        $text = strip_tags($text, '<' . implode('><', $this->allowedTags) . '>');

        $text = preg_replace_callback('/\{([^\}]+)\}/', function($match) {
            return strip_tags($match[0]);
        }, $text);

        $text = preg_replace_callback('/style="([^"]+)"/i', function($match) {
            $styles = explode(';', $match[1]);
            $keep = array_filter($styles, function($style) {
                list($style) = explode(':', $style);
                return in_array(strtolower(trim($style)), $this->allowedStyles);
            });
            if (count($keep) !== 0) {
                return 'style="' . implode(';', $keep) . '"';
            }
            return '';
        }, $text);

        return $text;
    }
}
