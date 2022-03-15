<?php

namespace App\V1\Models\DiscountCardType;

use App\V1\Models\BaseModel;
use App\V1\Models\DiscountCardType;
use App\V1\Traits\Models\HasConstraint;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class Icon extends BaseModel implements SharedResourceInterface
{
    use HasConstraint, SharedResource;

	const RELATION_TYPE = 'card_type_icon';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'card_type_icons';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'attachments',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'card_types',
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
     * Related card types
     * 
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */ 
    public function card_types()
    {
        return $this->hasMany(DiscountCardType::class, 'type_icon_id');
    }
}
