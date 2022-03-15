<?php

namespace App\V1\Models\TreatmentCourse;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Employee;
use App\V1\Models\TreatmentCourse;

class Document extends BaseModel implements SharedResourceInterface
{
    use SharedResource;
    
    const RELATION_TYPE = 'treatment_course_document';

    /**
     * @var string
     */
    protected $table = 'treatment_course_documents';

    /**
     * @var array
     */
    protected $fillable = [
        'treatment_course_id',
        'type',
        'attachments',
    ];

    /**
     * Related attachments
     *
     * @return \App\V1\Repositories\Relations\FileAttachment
     */
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }

    /**
     * Related treatment_course
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */ 
    public function treatment_course()
    {
        return $this->belongsTo(TreatmentCourse::class, 'treatment_course_id');
    }

    /**
     * Related signatures 
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signatures()
    {
        return $this->hasMany(Document\Signature::class, 'document_id')
            ->orderBy('created_at', 'desc');
    }
}