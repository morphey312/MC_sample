<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;
use App\V1\Models\Workspace;

class DaySheets extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'tabel';
    
    /**
     * @var string
     */
    protected $destTable = 'day_sheets';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_tabel';
    
    /**
     * @var bool
     */ 
    protected $mapRefs = false;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'date' => $this->toDate('date_'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'day_sheet_owner_id' => $this->fromRef('id_person', 'list_personal'),
            'day_sheet_owner_type' => ['is_human', function($v) {
                return $v == 1 ? Employee::RELATION_TYPE : Workspace::RELATION_TYPE;
            }],
        ], false, false);
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'tabel.id_tabel',
                'tabel.id_person',
                'tabel.date_',
                'tabel.datetime_begin',
                'tabel.datetime_end', 
                'list_personal.is_human',
                'list_personal.id_clinic',
            ]))
            ->leftJoin('list_personal', 'list_personal.id_person', '=', 'tabel.id_person');
            
        return $query;
    }
    
    /**
     * @inherit
     */ 
    protected function saveData($data, $row)
    {
        $existing = $this->getLocalConnection()
            ->table($this->destTable)
            ->where('date', $data['date'])
            ->where('clinic_id', $data['clinic_id'])
            ->where('day_sheet_owner_id', $data['day_sheet_owner_id'])
            ->where('day_sheet_owner_type', $data['day_sheet_owner_type'])
            ->first();
        
        if ($existing !== null) {
            return $existing->id;
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
        $timesheetData = $this->pickData($data, [
            'time_from' => $this->toTime('datetime_begin'),
            'time_to' => $this->toTime('datetime_end'),
        ], false, false) + [
            'day_sheet_id' => $localId,
        ];
        
        $id = $this->getLocalConnection()
            ->table('time_sheets')
            ->insertGetId($timesheetData);
        
        $specsializations = $this->getPivotData('tabel_specialization', 'id_tabel', $remoteId);
        if ($specsializations->count() !== 0) {
            $specsializationData = [];
            foreach ($specsializations as $specsialization) {
                $specsializationData[] = [
                    'specialization_id' => $this->getReference('list_specialization', $specsialization->id_specialization, true),
                ];
            }
            $this->savePivotData('specialization_time_sheet', 'time_sheet_id', $id, $specsializationData);
        }
    }
}