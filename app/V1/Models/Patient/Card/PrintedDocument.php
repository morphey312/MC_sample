<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\FileAttachment;

class PrintedDocument extends BaseRecordable
{
    const RELATION_TYPE = 'patient_card_printed_document';

    /**
     * @var array
     */
    protected $fillable = [
        'file_id',
        'document_name',
        'html',
        'header',
        'footer',
    ];

    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'patient_card_printed_documents';

    /**
     * Related file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(FileAttachment::class, 'file_id');
    }
}
