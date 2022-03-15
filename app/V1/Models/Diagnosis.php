<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Models\HasConstraint;

class Diagnosis extends BaseModel
{
    use HasConstraint;
    
    /**
     * @var array
     */
    protected $fillable = [
        'code',
        'description',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'diagnoses';
    
    /**
     * @var array
     */
    protected $deleting_constraints = [
        'outpatient_records',
    ];
    
    /**
     * Get display name
     * 
     * @return strinf
     */ 
    public function getDisplayNameAttribute()
    {
        return sprintf('%s - %s', $this->code, $this->i18n('description'));
    }
    
    /**
     * Related outpatient records
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */ 
    public function outpatient_records()
    {
        return $this->belongsToMany(Patient\Card\OutpatientRecord::class, 'outpatient_record_diagnosis_icd', 'diagnosis_id', 'record_id');
    }

    /**
     * Related outpatient records
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'diagnosis_id');
    }
}
