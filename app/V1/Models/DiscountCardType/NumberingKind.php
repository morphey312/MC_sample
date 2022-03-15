<?php

namespace App\V1\Models\DiscountCardType;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Models\Clinic;
use App\V1\Models\DiscountCardType;
use Illuminate\Support\Arr;

class NumberingKind extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    const TYPE_COMMON = 'card_numbering_common';
    const TYPE_CLINIC = 'card_numbering_clinic';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'card_numbering_kinds';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'unique',
        'clinics',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'unique' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'card_types',
    ];

    /**
     * @var array
     */
    public $clinicsToSave = null;

    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->clinicsToSave !== null) {
                $model->saveClinics($model->clinicsToSave);
            }
        });
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'card_numbering_clinic', 'number_kind_id', 'clinic_id')
                    ->withPivot('numbering_type', 'start_number', 'prefix', 'suffix')
                    ->orderBy('name');
    }

    /**
     * Save clinics
     *
     * @param array $data
     */
    public function saveClinics(array $data)
    {
        $this->clinics()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['clinic_id'],
                'data' => Arr::only($item, ['numbering_type', 'start_number', 'prefix', 'suffix']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Related card types
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function card_types()
    {
        return $this->hasMany(DiscountCardType::class, 'number_kind_id');
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
