<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;

class RecordTemplateField extends BaseRecordable
{
    /**
     * @var array
     */
    protected $fillable = [
        'enabled',
        'name',
        'label',
        'options'
    ];
    
    /**
     * @var array
     */ 
    protected $casts = [
        'enabled' => 'bool',
        'options' => 'array',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'template_fields';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * Related template
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function template()
    {
        return $this->belongsTo(RecordTemplate::class, 'template_id');
    }
    
    /**
     * Related field values
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function values()
    {
        return $this->hasMany(OutpatientRecordField::class, 'field_id');
    }
}
