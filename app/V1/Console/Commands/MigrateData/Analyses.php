<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class Analyses extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_service_analyse';
    
    /**
     * @var string
     */
    protected $destTable = 'analyses';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_analyse';
    
    /**
     * @var array
     */
    protected $laboratories = [];
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    public function __construct($command)
    {
        parent::__construct($command);
        
        $this->laboratories = $this->getLocalConnection()
            ->table('laboratories')
            ->pluck('id', 'name')
            ->mapWithKeys(function($value, $key) {
                $key = mb_strtolower($key);
                return [$key => $value];
            });
    }
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $result = $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'laboratory_code' => $this->toUTF('code_analyse'),
            'disabled' => $this->toBool('not_used'),
        ]);
        
        if (preg_match('#([^@]+)@(.+)#u', $result['name'], $matches)) {
            $result['name'] = trim($matches[1]);
            $result['laboratory_id'] = $this->getLaboratoryId(trim($matches[2], ' \\'));
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $existing = $this->getLocalConnection()
            ->table('analyses')
            ->where('name', '=', $data['name'])
            ->value('id');
        
        if ($existing !== null) {
            return $existing;
        }
        
        return parent::saveData($data, $row);
    }
    
    /**
     * Get laboratory ID by name
     * 
     * @param string $name
     * 
     * @return int
     */ 
    protected function getLaboratoryId($name)
    {
        $lowerName = mb_strtolower($name);
        
        if ($this->laboratories->has($lowerName)) {
            return $this->laboratories->get($lowerName);
        }
        
        $labId = $this->getLocalConnection()
            ->table('laboratories')
            ->insertGetId([
                'name' => $name,
                'company_id' => $this->companyId,
                'created_by_id' => self::$createdById,
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
        
        $this->laboratories[$lowerName] = $labId;
        
        return $labId;
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_service_analyse_clinic', 'id_analyse', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                    'code' => $this->toUTF('code'),
                    'duration_days' => ['cnt_day_for_execute', function($v) {
                        return $v > 0 ? $v : null;
                    }],
                ], false, false);
            }
            $this->savePivotData('analysis_clinics', 'analysis_id', $localId, $clinicData);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('analysis_clinics', 'analysis_id', $localId);
        $this->saveRelatedData($remoteId, $localId, $data);
    }
}