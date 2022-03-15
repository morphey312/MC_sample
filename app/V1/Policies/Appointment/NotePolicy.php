<?php

namespace App\V1\Policies\Appointment;

use App\V1\Policies\BasePolicy;

class NotePolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'appointment-notes';

    /**
     * @var array
     */
    protected $providedBy = [
        'appointment-notes.access' => [
            'execution-act.update-notes-tasks',
        ],
        'appointment-notes.create' => [
            'execution-act.update-notes-tasks',
            'execution-act.update-notes-tasks',
        ],
        'appointment-notes.update' => [
            'execution-act.update-notes-tasks',
        ],
    ];
}
