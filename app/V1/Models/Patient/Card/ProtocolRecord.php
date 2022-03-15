<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\FileAttachment;

class ProtocolRecord extends BaseRecordable
{
    const RELATION_TYPE = 'protocol_record';
    
    /**
     * @var array
     */
    protected $fillable = [
        'file_id',
        'template_id',
        'data',
    ];
    
    /**
     * @var string
     */ 
    protected $table = 'protocol_records';
    
    /**
     * @var bool
     */ 
    public $timestamps = false;
    
    /**
     * @var array
     */ 
    protected $casts = [
        'data' => 'object',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($model) {
            if ($model->file) {
                $model->file->delete();
            }
        });
    }
    
    /**
     * Related file
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function file()
    {
        return $this->belongsTo(FileAttachment::class, 'file_id');
    }
    
    /**
     * Related template
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function template()
    {
        return $this->belongsTo(ProtocolTemplate::class, 'template_id');
    }
}
