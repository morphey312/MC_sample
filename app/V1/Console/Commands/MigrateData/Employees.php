<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class Employees extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_personal';
    
    /**
     * @var string
     */
    protected $destTable = 'employees';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_person';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        if ($data->is_human == 1) {
            $this->destTable = 'employees';

            return $this->pickData($data, [
                'first_name' => ['fml', function($v) {
                    $fml = $this->convertStr($v, true);
                    $parts = explode(' ', $fml);
                    if (count($parts) > 1) {
                        return array_pop($parts);
                    }
                    return 'Mr.';
                }],
                'last_name' => ['fml', function($v) {
                    $fml = $this->convertStr($v, true);
                    $parts = explode(' ', $fml);
                    if (count($parts) > 1) {
                        array_pop($parts);
                    }
                    return implode(' ', $parts);
                }],
                'login' => $this->toUTF('username'),
                'password' => ['password_unreal', function($v) {
                    return bcrypt($v);
                }],
                'password_recovery' => ['password_unreal', function($v) {
                    return Crypt::encryptString($v);
                }],
            ]);
        } else {
            $this->destTable = 'workspaces';
            
            return $this->pickData($data, [
                'name' => $this->toUTF('fml'),
            ]) + [
                'has_day_sheet' => 1,
            ];
        }
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'list_personal.*', 
                'list_login.username', 
                'list_login.password_unreal',
            ]))
            ->leftJoin('list_login', 'list_login.id_person', '=', 'list_personal.id_person');
            
        return $query;
    }
    
    /**
     * @inherit
     */ 
    protected function saveData($data, $row)
    {
        if ($this->destTable === 'employees') {
            $existing = $this->getLocalConnection()
                ->table($this->destTable)
                ->where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->first();
            
            if ($existing !== null) {
                $id = $existing->id;
            } else {
                $id = $this->getLocalConnection()
                    ->table($this->destTable)
                    ->insertGetId(Arr::except($data, ['login', 'password', 'password_recovery']));
            }
            
            if (!empty($data['login'])) {
                $hasAccount = $this->getLocalConnection()
                    ->table('users')
                    ->where('userable_id', $id)
                    ->exists();
                
                if (!$hasAccount) {
                    $userData = Arr::only($data, ['login', 'password', 'password_recovery']);
                    $userData['userable_id'] = $id;
                    $userData['userable_type'] = Employee::RELATION_TYPE;
                    $userData['company_id'] = $this->companyId;
                    $userData['created_at'] = $this->timestamp();
                    $userData['updated_at'] = $this->timestamp();
                    
                    $this->getLocalConnection()
                        ->table('users')
                        ->insert($userData);
                }
            }
            
            return $id;
        } else {
            return $this->getLocalConnection()
                ->table($this->destTable)
                ->insertGetId($data);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchData($localId, $data, $row)
    {
        if ($this->destTable === 'employees') {
            return parent::patchData($localId, Arr::except($data, ['login', 'password', 'password_recovery']), $row);
        } else {
            return parent::patchData($localId, $data, $row);
        }
    }
}