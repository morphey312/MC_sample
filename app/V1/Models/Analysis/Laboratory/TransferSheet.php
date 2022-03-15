<?php

namespace App\V1\Models\Analysis\Laboratory;

use App\V1\Models\Analysis\Laboratory\Container;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\Analysis\Laboratory;
use App\V1\Models\Clinic;

class TransferSheet extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource;

    const STATUS_NEW = 'new';
    const STATUS_TRANSPORTING = 'transporting';
    const STATUS_RECIEVED = 'recieved';
    const RELATION_TYPE = 'transfer_sheet';

    /**
     * @var array
    */
    protected $table = 'transfer_sheets';

    /**
     * @var array
    */
    protected $fillable = [
        'external_transfer_id',
        'clinic_id',
        'status',
        'reciever_id',
        'reciever_name',
        'courier_id',
        'courier_name',
        'laboratory_id',
        'barcode',
        'sender_comment',
        'reciever_comment',
        'containers',
    ];

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->status === null) {
                $model->status = static::STATUS_NEW;
            }
        });
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return [$this->clinic_id];
    }

    /**
     * Related containers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function containers()
    {
        return $this->hasMany(Container::class, 'transfer_id');
    }

    /**
     * Related laboratory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
