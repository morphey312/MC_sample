<?php

namespace App\V1\Models\Appointment;

use App\V1\Models\BaseModel;
use App\V1\Models\Appointment;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Arr;

class Status extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_statuses';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'comment_required',
        'status_reason',
        'is_active',
        'service_in_cost',
        'patient_card_required',
        'service_in_order',
        'sms_for_card',
        'paint_cell',
        'color',
        'for_surgery',
        'has_delay',
        'delay',
        'delay_reasons',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'comment_required' => 'boolean',
        'status_reason' => 'boolean',
        'is_active' => 'boolean',
        'service_in_cost' => 'boolean',
        'patient_card_required' => 'boolean',
        'service_in_order' => 'boolean',
        'sms_for_card' => 'boolean',
        'paint_cell' => 'boolean',
        'for_surgery' => 'boolean',
        'has_delay' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'select_reasons',
        'appointments',
    ];

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'appointment_status_id');
    }

    /**
     * Related appointment status select reasons
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function select_reasons()
    {
        return $this->belongsToMany(Status\Reason::class, 'appointment_status_reason', 'appointment_status_id', 'status_reason_id')
            ->withPivot('default');
    }

    /**
     * Save select_reasons
     * 
     * @param array $data
     */ 
    public function saveReasons(array $data)
    {
        $this->select_reasons()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['id'],
                'data' => Arr::only($item, ['default']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Related appointment status select reasons
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function delay_reasons()
    {
        return $this->belongsToMany(Status\DelayReason::class, 'appointment_status_delay_reason', 'appointment_status_id', 'delay_reason_id');
    }
}
