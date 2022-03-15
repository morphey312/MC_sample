<?php

namespace App\V1\Models\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;
use App\V1\Models\Patient;
use Illuminate\Support\Arr;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\ModelNumber;

class Card extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    use ModelNumber;

    /**
     * @var array
     */
    protected $fillable = [
        'clinic',
        'patient',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'patient_cards';
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->pickNumber();
        });
    }
    
    /**
     * Related clinic
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    
    /**
     * Related patient
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    
    /**
     * Related records
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */ 
    public function records()
    {
        return $this->hasManyThrough(Card\Record::class, Card\Specialization::class, 'card_id', 'card_specialization_id');
    }
    
    /**
     * Related specializations
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function specializations()
    {
        return $this->hasMany(Card\Specialization::class, 'card_id');
    }
    
    /**
     * Get card number
     * 
     * @param int $specializationId
     * 
     * @return string
     */ 
    public function getNumber($specializationId)
    {
        foreach ($this->specializations as $specialization) {
            if ($specialization->specialization_id == $specializationId) {
                return sprintf('%d-%s', $this->number, $specialization->short_name);
            }
        }
        return null;
    }

    /**
     * Get card number
     *
     * @param int $specializationId
     *
     * @return string
     */
    public function getArchivedNumber($specializationId)
    {
        foreach ($this->archive_numbers as $archived_card) {
            if ($archived_card->specialization_id == $specializationId) {
                return $archived_card->number;
            }
        }
        return null;
    }

    /**
     * Save related card specializations
     * 
     * @param $specializations array
     */ 
    public function saveSpecializations($specializations = [])
    {
        $toSave = [];
        
        foreach ($specializations as $specialization) {
            $toSave[] = Arr::only($specialization, ['id', 'specialization_id']);
        }
        
        $relationsManager = $this->getRelationsManager();
        $relationsManager->assign('specializations', $toSave);
        $relationsManager->savePending();
    }

    /**
     * Related archive card numbers
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function archive_numbers()
    {
        return $this->hasMany(Card\ArchiveNumber::class, 'card_id');
    }
}
