<?php

namespace App\V1\Services\Merge;

use App\V1\Contracts\Services\Merge\Patient as MergeInterface;
use App\V1\Contracts\Repositories\PatientRepository;
use App\V1\Models\Patient\SignalRecord;
use App\V1\Observers\Audit\BaseAudit;
use App\V1\Models\Patient as GenericPatient;

class Patient implements MergeInterface
{
    /**
     * @var PatientRepository
     */
    protected $repository;
    
    /**
     * Service constructor
     *
     * @param  PatientRepository $repository
     */
    public function __construct(PatientRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * @inherit
     */ 
    public function merge($what, $where) 
    {
        $destination = $this->repository->find($where, false);
        if ($destination === null) {
            return false;
        }
        
        $source = $this->repository->getById($what);
        if ($source->count() === 0) {
            return false;
        }
        
        foreach ($source as $service) {
            $this->mergeInto($service, $destination);
        }
        
        if ($destination->isDirty()) {
            $destination->save();
        }
        
        return true;
    }
    
    /**
     * Merge two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     * 
     * @return bool
     */ 
    protected function mergeInto($source, $destination)
    {
        $destination->reparent($source, [
            'analysis_results',
            'appointment_services',
            'assigned_medicines',
            'call_logs',
            'call_logs_as_callee',
            'call_logs_as_caller',
            'process_logs',
            'relative_links',
            'services',
            'site_enquiries',
            'uploaded_documents',
            'service_prepayments',
            'insurance_act_services',
            'cache_validity',
            'user',
            'waitlist_records',
        ]);

        $destination->reparent($source, [
            'appointments',
            'call_requests',
            'calls',
            'payments',
            'treatment_courses',
            'insurance_policies',
        ], false);
        
        $this->mergeAccounts($source, $destination);
        $this->mergeCards($source, $destination);
        $this->mergeSignalRecords($source, $destination);
        $this->mergeDiscountCards($source, $destination);
        $this->mergeClinics($source, $destination);
        $this->mergeContacts($source, $destination);
        $this->mergeProfile($source, $destination);
        
        $source->delete();
    }
    
    /**
     * Merge accounts of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeAccounts($source, $destination)
    {
        foreach ($source->accounts as $account) {
            $destAccount = $destination->accounts
                ->where('clinic_id', $account->clinic_id)
                ->first();
            if ($destAccount !== null) {
                $destAccount->balance += $account->balance;
                $destAccount->save();
                $account->delete();
            } else {
                $account->patient_id = $destination->id;
                $account->save();
            }
        }
    }
    
    /**
     * Merge cards of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeCards($source, $destination)
    {
        if ($destination->cards->count() === 0) {
            foreach ($source->cards as $card) {
                $card->patient_id = $destination->id;
                $card->save();
            }
        } else {
            $destCard = $destination->cards->first();
            foreach ($source->cards as $card) {
                foreach ($card->specializations as $cardSpecialization) {
                    $destCardSpecialization = $destCard->specializations
                        ->where('specialization_id', $cardSpecialization->specialization_id)
                        ->first();
                    if ($destCardSpecialization !== null) {
                        $destCardSpecialization->reparent($cardSpecialization, [
                            'records',
                        ]);
                        $cardSpecialization->delete();
                    } else {
                        $cardSpecialization->card_id = $destCard->id;
                        $cardSpecialization->save();
                    }
                }
                $destCard->reparent($card, [
                    'archive_numbers',
                ]);
                $card->delete();
            }
        }
    }
    
    /**
     * Merge signal records of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeSignalRecords($source, $destination)
    {
        $sourceRecord = $source->signal_record;
        $destRecord = $destination->signal_record;
        
        if ($destRecord === null && $sourceRecord !== null) {
            $sourceRecord->patient_id = $destination->id;
            $sourceRecord->save();
        } elseif ($destRecord !== null && $sourceRecord !== null) {
            $attribs = array_diff($destRecord->getFillable(), [
                'patient_id',
                'diabetes',
                'transfusion',
            ]);
            
            foreach ($attribs as $attribute) {
                $destValue = $destRecord->getAttributeValue($attribute);
                if (empty($destValue) || $destValue === SignalRecord::N_A) {
                    $srcValue = $sourceRecord->getAttributeValue($attribute);
                    if (!empty($srcValue) && $srcValue !== SignalRecord::N_A) {
                        $destRecord->setAttribute($attribute, $srcValue);
                    }
                }
            }
            
            $sourceRecord->delete();
            if ($destRecord->isDirty()) {
                $destRecord->save();
            }
        }
    }
    
    /**
     * Merge discount cards of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeDiscountCards($source, $destination)
    {
        $destCards = $destination->issued_discount_cards;
        foreach ($source->issued_discount_cards as $card) {
            if ($destCards->where('id', $card->id)->first() === null) {
                $destCards->add($card);
                $destination->issued_discount_cards()->attach($card, [
                    'disabled' => $card->pivot->disabled, 
                    'is_owner' => $card->pivot->is_owner,
                ]);
            }
        }
    }
    
    /**
     * Merge profiles of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeProfile($source, $destination)
    {
        $attribs = array_diff($destination->getFillable(), [
            'status',
            'location',
            'mailing',
            'black_mark',
            'is_skk',
            'is_attention',
            'source_id',
            'contact_details',
            'clinics',
            'cards',
            'issued_discount_cards',
            'relatives',
        ]);

        $medInsuranceAttr = 'med_insurance';
        if ($source->med_insurance === GenericPatient::INSURANCE_YES) {
            array_push($attribs, $medInsuranceAttr);
        }
        
        foreach ($attribs as $attribute) {
            if ($attribute === $medInsuranceAttr) {
                $srcValue = $source->getAttributeValue($attribute);
                if ($srcValue === GenericPatient::INSURANCE_YES) {
                    $destination->setAttribute($attribute, $srcValue);
                    continue;
                }
            }

            $destValue = $destination->getAttributeValue($attribute);

            if (empty($destValue)) {
                $srcValue = $source->getAttributeValue($attribute);
                if (!empty($srcValue)) {
                    $destination->setAttribute($attribute, $srcValue);
                }
            }
        }
    }
    
    /**
     * Merge clinics of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeClinics($source, $destination)
    {
        $destClinics = $destination->clinics;
        foreach ($source->clinics as $clinic) {
            if ($destClinics->where('id', $clinic->id)->first() === null) {
                $destClinics->add($clinic);
                $destination->clinics()->attach($clinic);
            }
        }
    }
    
    /**
     * Merge contacts of two patients
     * 
     * @param \App\V1\Models\Patient $source
     * @param \App\V1\Models\Patient $destination
     */
    protected function mergeContacts($source, $destination)
    {
        $destContacts = $destination->contacts;
        foreach ($source->contacts as $contact) {
            $sameContact = $destContacts
                ->where('type', $contact->type)
                ->where('primary', $contact->primary)
                ->first();
            
            if ($sameContact === null) {
                $destContacts->add($contact);
                $contact->patient_id = $destination->id;
                $contact->save();
            } else {
                $contact->delete();
            }
        }
    }
}