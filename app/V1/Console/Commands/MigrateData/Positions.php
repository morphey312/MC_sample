<?php

namespace App\V1\Console\Commands\MigrateData;

class Positions extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_type';
    
    /**
     * @var string
     */
    protected $destTable = 'positions';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_type';
    
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
            'name' => $this->toUTF('description'),
            'is_operator' => $this->toBool('is_operator'),
            'has_voip' => $this->toBool('is_operator'),
            'is_doctor' => $this->toBool('is_doctor'),
            'has_specialization' => $this->toBool('is_doctor'),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
            'list_type.*',
            'IIF(EXISTS(SELECT * FROM list_personal AS lp1 WHERE lp1.id_type = list_type.id_type AND lp1.is_operator = 1), 1, 0) as is_operator',
            'IIF(EXISTS(SELECT * FROM list_personal AS lp2 INNER JOIN list_person_specialization ON lp2.id_type = list_type.id_type AND list_person_specialization.id_person = lp2.id_person), 1, 0) AS is_doctor',
        ]));
        
        return $query;
    }
    
}