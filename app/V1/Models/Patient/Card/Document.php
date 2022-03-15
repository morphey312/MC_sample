<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\Employee;

class Document extends BaseRecordable
{
    const RELATION_TYPE = 'patient_card_document';

    /**
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'is_questionnaire',
        'attachments',
        'name'
    ];

    /**
     * @var string
     */
    protected $table = 'patient_card_documents';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Related doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Related attachments
     *
     * @return \App\V1\Repositories\Relations\FileAttachment
     */
    public function attachments()
    {
        return $this->fileAttachment('attachments');
    }
}
