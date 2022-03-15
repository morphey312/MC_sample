<?php

namespace App\V1\Contracts\Elastic\Report;

interface CallCenterDocumentPrefix
{
    const PREFIX_CALL = 'cc_';
    const PREFIX_APPOINTMENT_CALL = 'ac_';
    const PREFIX_TREATMENT = 'at_';
    const PREFIX_INCOME = 'ai_';
    const PREFIX_APPOINTMENT_FIRST = 'af_';
    const PREFIX_APPOINTMENT_REPEATED = 'ar_';
}