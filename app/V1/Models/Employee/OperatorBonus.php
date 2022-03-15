<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee;
use App\V1\Models\Clinic;
use Illuminate\Support\Arr;

class OperatorBonus extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    /**
     * @var array
     */
    protected $fillable = [
        'operator_id',
        'evaluation',
        'clinics',
    ];
    
    /**
     * @var array
     */ 
    protected $casts = [
        'evaluation' => 'float',
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
     * Related operator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operator()
    {
        return $this->belongsTo(Employee::class, 'operator_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_operator_bonus', 'operator_bonus_id', 'clinic_id')
                    ->withPivot('mistakes')
                    ->orderBy('name');
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
     * Save clinics
     * 
     * @param array $data
     */
    public function saveClinics(array $data)
    {
        $this->clinics()->sync(Arr::pluck(array_map(function ($item) {
            return [
                'id' => $item['clinic_id'],
                'data' => Arr::only($item, ['mistakes']),
            ];
        }, $data), 'data', 'id'));
    }

}
