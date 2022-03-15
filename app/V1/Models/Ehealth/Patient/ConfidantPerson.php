<?php

namespace App\V1\Models\Ehealth\Patient;

use App\V1\Models\BaseModel;
use App\V1\Models\Ehealth\Patient;
use App\V1\Models\Ehealth\Patient\Document;
use App\V1\Models\Ehealth\Patient\RelationshipDocument;
use Auth;

class ConfidantPerson extends BaseModel
{
    const RELATION_TYPE = 'confidant_person';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'ehealth_patient_confidant_persons';

    /**
     * @var array
     */
    protected $fillable = [
        'relation_type',
        'ehealth_patient_id',
        'first_name',
        'last_name',
        'second_name',
        'birth_country',
        'birth_settlement',
        'birth_date',
        'gender',
        'phone_number',
        'phone_type',
        'email',
        'preferred_way_communication',
        'secret',
        'tax_id',
        'unzr',
        'patient_documents',
        'patient_relationship_documents',
    ];


    /**
     * @var array
     */
    protected $dates = [
        'birthday',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'patient_documents',
        'patient_relationship_documents',
    ];

    /**
     * Related patient documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_documents()
    {
        return $this->morphMany(Document::class, 'owner');
    }

    /**
     * Related patient authentications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_relationship_documents()
    {
        return $this->hasMany(RelationshipDocument::class, 'confidant_person_id');
    }

    /**
     * Related employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'ehealth_patient_id');
    }
}
