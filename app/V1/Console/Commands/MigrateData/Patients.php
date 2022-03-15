<?php

namespace App\V1\Console\Commands\MigrateData;

class Patients extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_patient';
    
    /**
     * @var string
     */
    protected $destTable = 'patients';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_patient';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'firstname' => $this->toUTF('name_'),
            'lastname' => $this->toUTF('family'),
            'middlename' => $this->toUTF('mid_name'),
            'gender' => $this->fromMap('sex', [
                '1' => 'male',
                '2' => 'female',
            ]),
            'status' => ['is_patient', function($v) {
                return $v == 1 ? 'patient' : 'guest';
            }],
            'med_insurance' => ['is_insurance', function($v) {
                return $v == 1 ? 'yes' : 'no';
            }],
            'location' => $this->toUTF('location'),
            'birthday' => $this->toDate('date_birth'),
            'comment' => $this->toUTF('note'),
            'source_id' => $this->fromRef('id_source_information', 'list_source_information'),
            'mailing' => $this->toBool('not_mailing', true),
            'mailing_analysis' => $this->toBool('not_mailing', true),
            'black_mark' => $this->toBool('is_black_label'),
            'black_mark_reason' => $this->fromRef('id_reason_set_black_label', 'list_reason_set_black_label', true),
            'black_mark_comment' => $this->toUTF('note_black_label'),
            'is_skk' => $this->toBool('is_white_label'),
            'skk_reason' => $this->fromRef('id_reason_set_white_label', 'list_reason_set_white_label', true),
            'skk_comment' => $this->toUTF('note_white_label'),
            'is_attention' => $this->toBool('is_attention_label'),
            'attention_comment' => $this->toUTF('note_attention_label'),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'list_patient.*',
                'list_residence.description AS location',
            ]))
            ->leftJoin('list_residence', 'list_residence.id_residence', '=', 'list_patient.id_residence');
            
        return $query;
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $contacts = array_filter([
            trim($row->phone),
            trim($row->phone2),
        ]);
        
        if (count($contacts) !== 0) {
            $existing = $this->getLocalConnection()
                ->table('patient_contacts')
                ->whereIn('value', $contacts)
                ->first();
            
            if ($existing !== null) {
                $patient = (array) $this->getLocalConnection()
                    ->table($this->destTable)
                    ->find($existing->patient_id);
                
                if ($this->isSamePerson($patient, $data)) {
                    return $existing->patient_id;
                }
            }
        }
        
        return $this->getLocalConnection()
            ->table($this->destTable)
            ->insertGetId($data);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $contactsData = [];
        
        if ($data->phone) {
            $phoneInfo = $this->parsePhone(trim($data->phone));
            
            if ($phoneInfo !== null) {
                $contactsData[] = [
                    'type' => 'phone',
                    'primary' => 1,
                    'value' => $phoneInfo[0],
                    'comment' => $phoneInfo[1],
                    'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone1', true),
                    'patient_id' => $localId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ];
            }
        }
        
        if ($data->phone2) {
            $phoneInfo = $this->parsePhone(trim($data->phone2));
            
            if ($phoneInfo !== null) {
                $contactsData[] = [
                    'type' => 'phone',
                    'primary' => 0,
                    'value' => $phoneInfo[0],
                    'comment' => $phoneInfo[1],
                    'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone2', true),
                    'patient_id' => $localId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ];
            }
        }
        
        if (strpos($data->email, '@') !== false) {
            $contactsData[] = [
                'type' => 'email',
                'primary' => 0,
                'value' => $this->convertStr($data->email),
                'comment' => null,
                'clinic_id' => null,
                'patient_id' => $localId,
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ];
        }
        
        if (count($contactsData) !== 0) {
            $this->getLocalConnection()
                ->table('patient_contacts')
                ->insert($contactsData);
        }
        
        $this->saveRelatedClinics($remoteId, $localId, $data);
    }
    
    /**
     * Save related clinics data
     * 
     * @param int $remoteId
     * @param int $localId
     * @param object $data
     */ 
    protected function saveRelatedClinics($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_patient_clinic', 'id_patient', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = [
                    'clinic_id' => $this->getReference('list_clinic', $clinic->id_clinic, true),
                ];
            }
            $this->savePivotData('patient_clinics', 'patient_id', $localId, $clinicData);
        }
    }
    
    /**
     * Check if two persons are the same on
     * 
     * @param array $person1
     * @param array $person2
     * 
     * @return bool
     */ 
    protected function isSamePerson($person1, $person2) 
    {
        $propsToCompare = [
            'firstname',
            'lastname',
            'middlename',
            'gender',
            'birthday',
        ];
        
        foreach ($propsToCompare as $prop) {
            $value1 = trim($person1[$prop]);
            $value2 = trim($person2[$prop]);
            
            if ($value1 !== '' && $value2 !== '' && $value1 !== $value2) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Parse phone to have correct format
     * 
     * @param string $phone
     * 
     * @return array
     */ 
    protected function parsePhone($phone)
    {
        if (preg_match('#([0-9]{7,12})($|[^0-9].*$)#u', $phone, $matches)) {
            return [
                $matches[1],
                $this->convertStr($matches[2]),
            ];
        }
        return null;
    }
    
    protected function getLogTable()
    {
        return 'list_patient_log';
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('patient_clinics', 'patient_id', $localId);
        $this->saveRelatedClinics($remoteId, $localId, $data);
        
        $contactsData = [];
        
        if ($data->phone) {
            $phoneInfo = $this->parsePhone(trim($data->phone));
            
            if ($phoneInfo !== null) {
                $existing = $this->getLocalConnection()
                    ->table('patient_contacts')
                    ->where('patient_id', $localId)
                    ->where('type', 'phone')
                    ->where('primary', 1)
                    ->value('id');
                
                if ($existing) {
                    $this->getLocalConnection()
                        ->table('patient_contacts')
                        ->where('id', $existing)
                        ->update([
                            'value' => $phoneInfo[0],
                            'comment' => $phoneInfo[1],
                            'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone1', true),
                            'updated_at' => $this->timestamp(),
                        ]);
                } else {
                    $contactsData[] = [
                        'type' => 'phone',
                        'primary' => 1,
                        'value' => $phoneInfo[0],
                        'comment' => $phoneInfo[1],
                        'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone1', true),
                        'patient_id' => $localId,
                        'created_at' => $this->timestamp(),
                        'updated_at' => $this->timestamp(),
                    ];
                }
            }
        }
        
        if ($data->phone2) {
            $phoneInfo = $this->parsePhone(trim($data->phone2));
            
            if ($phoneInfo !== null) {
                $existing = $this->getLocalConnection()
                    ->table('patient_contacts')
                    ->where('patient_id', $localId)
                    ->where('type', 'phone')
                    ->where('primary', 0)
                    ->value('id');
                
                if ($existing) {
                    $this->getLocalConnection()
                        ->table('patient_contacts')
                        ->where('id', $existing)
                        ->update([
                            'value' => $phoneInfo[0],
                            'comment' => $phoneInfo[1],
                            'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone2', true),
                            'updated_at' => $this->timestamp(),
                        ]);
                } else {
                    $contactsData[] = [
                        'type' => 'phone',
                        'primary' => 0,
                        'value' => $phoneInfo[0],
                        'comment' => $phoneInfo[1],
                        'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone2', true),
                        'patient_id' => $localId,
                        'created_at' => $this->timestamp(),
                        'updated_at' => $this->timestamp(),
                    ];
                }
            }
        }
        
        if (strpos($data->email, '@') !== false) {
            $existing = $this->getLocalConnection()
                ->table('patient_contacts')
                ->where('patient_id', $localId)
                ->where('type', 'email')
                ->value('id');
            
            if ($existing) {
                $this->getLocalConnection()
                    ->table('patient_contacts')
                    ->where('id', $existing)
                    ->update([
                        'value' => $this->convertStr($data->email),
                        'updated_at' => $this->timestamp(),
                    ]);
            } else {
                $contactsData[] = [
                    'type' => 'email',
                    'primary' => 0,
                    'value' => $this->convertStr($data->email),
                    'comment' => null,
                    'clinic_id' => null,
                    'patient_id' => $localId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ];
            }
        }
        
        if (count($contactsData) !== 0) {
            $this->getLocalConnection()
                ->table('patient_contacts')
                ->insert($contactsData);
        }
    }
}