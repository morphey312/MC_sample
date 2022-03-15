<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Models\PriceAgreementAct\Price;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasPrice;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Arr;
use App\V1\Contracts\Services\Permissions\ClinicShared;

class Analysis extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasPrice, HasConstraint;

    const RELATION_TYPE = 'analysis';

    /**
     * @var array
     *
    */
    protected $fillable = [
        'name',
        'laboratory_code',
        'description',
        'laboratory_id',
        'disabled',
        'clinics',
        'candidate_id',
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
    protected $deleting_constraints = [
        'results',
        'prices',
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
        return $this->belongsToMany(Clinic::class, 'analysis_clinics', 'analysis_id', 'clinic_id')
                    ->withPivot('code', 'duration_days')
                    ->orderBy('name');
    }

    /**
     * Related laboratory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function laboratory()
    {
        return $this->belongsTo(Analysis\Laboratory::class, 'laboratory_id');
    }

    /**
     * Related appointment results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_results()
    {
        return $this->hasMany(Analysis\Result::class, 'analysis_id')
            ->whereNotNull('appointment_id');
    }

    /**
     * Related results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(Analysis\Result::class, 'analysis_id');
    }
    /**
     * Related candidate from LIS
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function candidate()
    {
        return $this->BelongsTo(Analysis\Candidate::class, 'candidate_id');
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
                'data' => Arr::only($item, ['code', 'duration_days']),
            ];
        }, $data), 'data', 'id'));
    }

    /**
     * Get related clinic by id
     *
     * @param int $clinicId
     *
     * @return entity
     */
    public function getClinic($clinicId)
    {
        return $this->clinics()
            ->where('id', '=', $clinicId)
            ->first();
    }

    /**
     * @inherit
     */
    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
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

    /**
     * Related prices
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function act_prices()
    {
        return $this->morphMany(\App\V1\Models\PriceAgreementAct\Price::class, 'service')->with('clinics');
    }
}
