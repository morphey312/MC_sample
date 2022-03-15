<?php

namespace App\V1\Policies\Analysis;

use App\V1\Policies\ClinicSharedPolicy;
use App\V1\Models\User;

class ResultPolicy extends ClinicSharedPolicy
{
    const ACTION_SUBMIT = 'submit-result';

    /**
     * @var  string
     */
    protected $module = 'analysis-results';

    /**
     * @var array
     */
    protected $providedBy = [
        'analysis-results.create' => [
            'analysis-results.create-result',
            'patient-cabinet.outclinic-analysis-add'
        ],
        'analysis-results.update' => [
            'analysis-results.update-result',
            'patient-cabinet.analysis-date-ready-set',
            'patient-cabinet.analysis-date-email-sent-set',
        ],
        'analysis-results.update-clinic' => [
            'analysis-results.update-result-clinic',
        ],
        'analysis-results.delete-clinic' => [
            'doctor-cabinet.assign-analyses',
        ],
        'analysis-results.access' => [
            'patient-cabinet.analyses',
        ],
        'analysis-results.delete' => [
            'patient-cabinet.outclinic-analysis-delete'
        ],
    ];

    /**
     * Check if user can submit results
     *
     * @param User $user
     *
     * @return bool
     */
    public function submit(User $user)
    {
        return $this->can($user, self::ACTION_SUBMIT);
    }
}
