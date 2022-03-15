<?php

namespace App\V1\Observers;

use App\V1\Mailing\Patient\PatientSavedMessage;
use App\V1\Models\Patient;
use MailingMessenger;

class PatientObserver
{
    /**
     * Listen to updated event
     *
     * @param Patient $patient
     */
    public function updated(Patient $patient)
    {
        if ($patient->isDirty('is_confirmed')) {
            if ($patient->is_confirmed && $patient->has_registration) {
                $user = Patient\User::where('patient_id', $patient->id)->first();
                $registration = $user ? Patient\Registration::where('user_id', $user->id)->first() : null;

                if ($registration) {
                    $registration->status = Patient\Registration::STATUS_CONFIRMED;
                    $registration->save();
                }
            }
        }

    }

    /**
     * Listen to saved event
     *
     * @param Patient $patient
     */
    public function saved(Patient $patient)
    {
        MailingMessenger::send(new PatientSavedMessage($patient));
    }
}
