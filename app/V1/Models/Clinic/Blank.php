<?php

namespace App\V1\Models\Clinic;

use App\V1\Models\BaseModel;
use App\V1\Models\Clinic;

class Blank extends BaseModel
{
    const RELATION_TYPE = 'clinic_blank';

    const TYPE_HEADER = 'header';
    const TYPE_FOOTER = 'footer';

    /**
     * Specifying table name
     * 
     * @var string
     */
    protected $table= 'clinic_blanks';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'clinic_id',
        'type',
        'attachments',
    ];

    /**
     * Related clinic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
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