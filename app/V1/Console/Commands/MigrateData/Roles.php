<?php

namespace App\V1\Console\Commands\MigrateData;

class Roles extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_type';
    
    /**
     * @var string
     */
    protected $destTable = 'roles';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_type';
    
    /**
     * @inherit
     */ 
    protected function getProgressKey()
    {
        return sprintf('data_migration_%s_roles', $this->srcTable);
    }
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $employees = $this->getPivotData('list_personal', 'id_type', $remoteId, function($query) {
            $query->whereNotIn('id_clinic', self::$excludeClinicIds);
            return $query;
        });
        
        if ($employees->count() !== 0) {
            $employeesData = [];
            foreach ($employees as $employee) {
                $employeeId = $this->getReference('list_personal', $employee->id_person);
                if (!$employeeId) {
                    continue;
                }
                
                $userId = $this->getLocalConnection()
                    ->table('users')
                    ->where('userable_id', $employeeId)
                    ->value('id');
                if (!$userId) {
                    continue;
                }
                
                $employeesData[] = [
                    'user_id' => $userId,
                ];
            }
            $this->savePivotData('user_roles', 'role_id', $localId, $employeesData);
        }
    }
}