<?php

namespace App\V1\Policies;

class PatientDocumentPolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'patient-documents';

    protected $providedBy = [
        'patient-documents.access' => [
            'doctor-cabinet.issue-document',
        ],
    ];
}
