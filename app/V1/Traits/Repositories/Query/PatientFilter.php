<?php

namespace App\V1\Traits\Repositories\Query;

use App\V1\Models\Patient\Contact;

trait PatientFilter
{
    /**
     * Filter by patient name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientName($query, $value)
    {
        $value = $this->safeString($value);

        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients')
                ->whereContains('patients.lastname', $value)
                ->orWhereContains('patients.firstname', $value)
                ->orWhereContains('patients.middlename', $value);
        });
    }

    /**
     * Filter by patient phone number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientPhoneNumber($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_contacts.patient_id')
                ->from('patient_contacts')
                ->where('patient_contacts.type', '=', Contact::TYPE_PHONE);
            $this->filterAttribute($query, 'value', $value);
        });
    }

    /**
     * Filter by patient card number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientCardNumber($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_cards.patient_id')
                ->from('patient_cards');
            $this->filterAttribute($query, 'patient_cards.number', $value);
        });
    }

    /**
     * Filter by patient archive card number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientArchiveCardNumber($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_cards.patient_id')
                ->from('patient_cards')
                ->join('archive_card_numbers', 'archive_card_numbers.card_id', '=', 'patient_cards.id');
            $this->filterAttribute($query, 'archive_card_numbers.number', $value);
        });
    }

    /**
     * Filter by patient cource
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientSource($query, $value)
    {
        // TODO: pick up best case

        //~ $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            //~ $query->select('patients.id')
                //~ ->from('patients')
                //~ ->whereIn('patients.source_id', $this->safeArray($value));
        //~ });

        //~ $query->join('patients', function($join) use($value) {
            //~ $join->on('patients.id', '=', 'patient_id')
                //~ ->whereIn('patients.source_id', $this->safeArray($value));
        //~ });

        $query->whereHas('patient', function($query) use($value) {
            $query->whereIn($query->qualifyColumn('source_id'), $this->safeArray($value));
        });
    }

    /**
     * Filter by patient id
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatient($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), $this->safeArray($value));
    }

    /**
     * Filter by patient primary phone number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientPrimaryPhoneNumber($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_contacts.patient_id')
                ->from('patient_contacts')
                ->where('patient_contacts.primary', '=', 1)
                ->where('patient_contacts.type', '=', Contact::TYPE_PHONE);
            $this->filterAttribute($query, 'value', $value);
        });
    }

    /**
     * Filter by patient secondary phone number
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientSecondaryPhoneNumber($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_contacts.patient_id')
                ->from('patient_contacts')
                ->where('patient_contacts.primary', '!=', 1)
                ->where('patient_contacts.type', '=', Contact::TYPE_PHONE);
            $this->filterAttribute($query, 'value', $value);
        });
    }

    /**
     * Filter by patient email
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientEmail($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patient_contacts.patient_id')
                ->from('patient_contacts')
                ->where('patient_contacts.type', '=', Contact::TYPE_EMAIL);
            $this->filterAttribute($query, 'value', $value);
        });
    }

    /**
     * Filter by patient location
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     */
    public function filterPatientLocation($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterAttribute($query, 'patients.location', $value);
        });
    }

    /**
     * Filter by patient firstname
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientFirstname($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterAttribute($query, 'patients.firstname', $value);
        });
    }

    /**
     * Filter by patient lastname
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientLastname($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterAttribute($query, 'patients.lastname', $value);
        });
    }

    /**
     * Filter by patient middlename
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientMiddlename($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterAttribute($query, 'patients.middlename', $value);
        });
    }

    /**
     * Filter by patient birthdate
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientBirthdate($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterDateAttribute($query, 'patients.birthday', $value);
        });
    }

    /**
     * Filter by patient birthdate low threshold
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientBirthdateFrom($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterDateAttribute($query, 'patients.birthday', $value, '>=');
        });
    }

    /**
     * Filter by patient birthdate high threshold
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPatientBirthdateTo($query, $value)
    {
        $query->whereIn($query->qualifyColumn('patient_id'), function($query) use($value) {
            $query->select('patients.id')
                ->from('patients');
            $this->filterDateAttribute($query, 'patients.birthday', $value, '<=');
        });
    }
}
