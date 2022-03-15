<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Patient\Card;
use App\V1\Models\Specialization;

class ArchiveNumber extends BaseModel
{

    const RELATION_TYPE = 'archive_card';

    /**
     * Model table
     *
     * @var string
     */
    protected $table = 'archive_card_numbers';

    /**
     * @var array
     */
    protected $fillable = [
        'attachments'
    ];
    
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    /**
     * Related specialization
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient_card()
    {
        return $this->belongsTo(Card::class, 'card_id');
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
