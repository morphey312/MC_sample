<?php

namespace App\V1\Models\Msp;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Models\Msp;
use App\V1\Models\Clinic;
use App\V1\Models\FileAttachment;
use App\V1\Models\Ehealth\User;

class Contract extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const RELATION_TYPE = 'msp_contract';
    
    const STATUS_NEW = 'NEW';
    const STATUS_IN_PROCESS = 'IN_PROCESS';
    const STATUS_DECLINED = 'DECLINED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_PENDING_NHS_SIGN = 'PENDING_NHS_SIGN';
    const STATUS_NHS_SIGNED = 'NHS_SIGNED';
    const STATUS_SIGNED = 'SIGNED';
    const STATUS_TERMINATED = 'TERMINATED';
    const STATUS_VERIFIED = 'VERIFIED';

    /**
     * @var string
     */ 
    protected $table = 'msp_contracts';

    /**
     * @var array
     */
    protected $fillable = [
        'msp_id',
        'type',
        'contractor_base',
        'payer_account_number',
        'payer_mfo',
        'payer_bank',
        'start_date',
        'end_date',
        'form_type',
        'contract_number',
        'medical_program',
        'statute_id',
        'additional_document_id',
        'clinics',
        'subcontractors',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'sent_to_ehealth_at',
    ];

    /**
     * @var array
     */ 
    protected $casts = [
        'medical_program' => 'object',
    ];

    /**
     * Related MSP
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function msp()
    {
        return $this->belongsTo(Msp::class, 'msp_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'msp_contract_clinics', 'contract_id', 'clinic_id');
    }

    /**
     * Related statute document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statute()
    {
        return $this->belongsTo(FileAttachment::class, 'statute_id');
    }

    /**
     * Related additional document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function additional_document()
    {
        return $this->belongsTo(FileAttachment::class, 'additional_document_id');
    }

    /**
     * Related subcontractors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subcontractors()
    {
        return $this->hasMany(Contract\Contractor::class, 'contract_id');
    }

    /**
     * Related ehealth user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ehealth_user()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'user_id');
    }
}
