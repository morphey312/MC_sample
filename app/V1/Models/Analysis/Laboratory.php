<?php

namespace App\V1\Models\Analysis;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Analysis;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Arr;

class Laboratory extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'disabled',
        'clinics',
        'external_id',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'analyses',
    ];

    /**
     * @var array
     *
     */
    protected $casts = [
        'disabled' => 'boolean',
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
        return $this->belongsToMany(Clinic::class, 'laboratories_clinics', 'laboratory_id', 'clinic_id')
            ->withPivot('priority');
    }

    /**
     * Related analyses
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'laboratory_id');
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
                'data' => Arr::only($item, ['priority']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Set clinics to save
     *
     * @param mixed $value
     */
    public function setClinicsAttribute($value)
    {
        $this->clinicsToSave = $value;
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
