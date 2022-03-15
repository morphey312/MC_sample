<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;

class OutpatientRecordField extends BaseRecordable
{
    /**
     * @var array
     */
    protected $fillable = [
        'field_id',
        'option_value',
        'value',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'outpatient_record_fields';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * Related record
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function record()
    {
        return $this->belongsTo(OutpatientRecord::class, 'record_id');
    }
    
    /**
     * Related field template
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function field_template()
    {
        return $this->belongsTo(RecordTemplateField::class, 'field_id');
    }
    
    /**
     * Set option value(s)
     * 
     * @param mixed $val
     */ 
    public function setOptionValueAttribute($val)
    {
        $this->attributes['option_value'] = is_array($val) ? implode(',', $val) : $val;
    }
    
    /**
     * Get option value(s)
     * 
     * @param string $val
     * 
     * @return mixed
     */ 
    public function getOptionValueAttribute($val)
    {
        if ($val === null) {
            return null;
        }
        return strpos($val, ',') !== false 
            ? array_map('intval', explode(',', $val)) 
            : intval($val);
    }
}
