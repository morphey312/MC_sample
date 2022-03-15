<?php


namespace App\V1\Models\Patient;

use App\V1\Models\Appointment;
use App\V1\Models\BaseModel;
use App\V1\Models\FileAttachment;
use App\V1\Models\Patient;
use App\V1\Models\Employee;

class AppointmentDocument extends BaseModel
{
    const DOCUMENT_ACTS = 'acts';
    const DOCUMENT_PAYMENTS = 'payment';

    /**
     * @var array
     */
    protected $fillable = [
        'file_id',
        'patient_id',
        'appointment_id',
        'assigner_id',
        'type',
        'number',
        'url'
    ];

    /**
     * @var string
     */
    protected $table = 'appointment_patient_documents';

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
     * Related patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Related appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Related assigner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner()
    {
        return $this->belongsTo(Employee::class, 'assigner_id');
    }
}
