<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Specialization;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Facades\FormTemplateCompiler;

class RecordTemplate extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;
    
    /**
     * @var string
     */ 
    protected $table = 'card_record_templates';
    
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'template',
        'is_fallback',
    ];
    
    /**
     * @var array
     */ 
    protected $casts = [
        'structure' => 'array',
        'is_fallback' => 'boolean',
    ];
    
    /**
     * @var array
     */
    protected $deleting_constraints = [
        'specializations',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            $model->compile();
        });
        
        static::saved(function ($model) {
            $model->deleteUnusedFields();
        });
    }
    
    /**
     * Related specializations
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function specializations()
    {
        return $this->hasMany(Specialization::class, 'card_template_id');
    }

     /**
     * Related specializations addons
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization_addons()
    {
        return $this->belongsToMany(Specialization::class, 'specialization_record_template', 'card_template_id', 'specialization_id');
    }
    
    /**
     * Related fields
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function fields()
    {
        return $this->hasMany(RecordTemplateField::class, 'template_id');
    }
    
    /**
     * Related enabled fields
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function enabled_fields()
    {
        return $this->fields()->where('enabled', 1);
    }
    
    /**
     * Do template compilation
     */ 
    protected function compile()
    {
        $compiled = FormTemplateCompiler::compile($this->template);
        $fields = [];
        if ($this->exists) {
            foreach ($this->fields as $field) {
                $fields[$field->name] = $field;
                $field->enabled = false;
            }
        }
        foreach ($compiled['fields'] as $field) {
            if (isset($fields[$field['name']])) {
                $fieldModel = $fields[$field['name']];
            } else {
                $fieldModel = new RecordTemplateField();
                $fieldModel->name = $field['name'];
                $fields[$fieldModel->name] = $fieldModel;
            }
            $fieldModel->enabled = true;
            $fieldModel->label = $field['hint'] ?? $field['label'];
            $fieldModel->options = $field['options'] ?? null;
        } 
        $this->structure = $compiled['structure'];
        $this->fields = array_values($fields);
    }
    
    /**
     * Delete unused fields
     */ 
    protected function deleteUnusedFields()
    {
        $this->fields()
            ->where('enabled', '!=', 1)
            ->whereDoesntHave('values')
            ->delete();
    }
}
