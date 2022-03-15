<?php

namespace App\V1\Console\Commands\MigrateData;

use DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Exception;

class BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable;
    
    /**
     * @var string
     */
    protected $destTable;
    
    /**
     * @var string
     */
    protected $remoteKey;
    
    /**
     * @var App\V1\Console\Commands\MigrateDataCommand
     */
    protected $command;
    
    /**
     * @var int
     */ 
    protected $chunkSize = 30;
    
    /**
     * @var array
     */ 
    protected $refsCache = [];
    
    /**
     * @var int
     */ 
    protected $companyId = 1;

    /**
     * @var int
     */
    protected $basePriceSetId = 1;
    
    /**
     * @var bool
     */ 
    protected $mapRefs = true;
    
    /**
     * @var bool
     */  
    protected $shouldPatch = false;
    
    /**
     * @var array
     */ 
    protected static $excludeClinicIds = null;

    /**
     * @var int
     */
    protected static $createdById = null;
    
    /**
     * @var bool
     */ 
    public static $showProgress = true;

    /**
     * @var bool
     */ 
    public static $convertEncoding = true;
    
    /**
     * Constructor
     * 
     * @param App\V1\Console\Commands\MigrateDataCommand $command
     */ 
    public function __construct($command)
    {
        $this->command = $command;
    }
    
    /**
     * @return \Illuminate\Database\Connection
     */ 
    protected function getRemoteConnection()
    {
        return DB::connection('sqlsrv');
    }
    
    /**
     * @return \Illuminate\Database\Connection
     */ 
    protected function getLocalConnection()
    {
        return DB::connection('mysql');
    }
    
    /**
     * Setup migration
     */ 
    public function setup()
    {
        if (self::$excludeClinicIds === null) {
            self::$excludeClinicIds = $this->getRemoteConnection()
                ->table('list_clinic')
                // ->where('is_not_active', 1)
                ->pluck('id_clinic')
                ->all();
        }
        if (self::$createdById === null) {
            self::$createdById = $this->getLocalConnection()
                ->table('users')
                ->where('login', 'admin_slovak')
                ->value('id');
            if (!self::$createdById) {
                throw new Exception('User `admin_slovak` not exists');
            }
        }
    }
    
    /**
     * Shutdown migration
     */ 
    public function shutdown()
    {
        unset($this->refsCache);
    }
    
    /**
     * Run migration from this source
     * 
     * @param bool $continue
     */ 
    public function migrate($continue = true)
    {
        $this->command->info(sprintf('Migrating data from "%s" to "%s"...', $this->srcTable, $this->destTable));
        
        if ($continue) {
            $startFrom = $this->getPreviousProgress();
        } else {
            $startFrom = false;
        }
        
        $totalLeft = $this->getTotalLeft($startFrom);
        $totalDone = 0;
        
        if ($totalLeft == 0) {
            $this->command->info('Nothing new to get from there');
            return;
        }
        
        if (self::$showProgress) {
            $bar = $this->command->createProgressBar($totalLeft);
        }
        
        while ($totalDone < $totalLeft) {
            $chunk = $this->getNextChunk($startFrom, $this->chunkSize);
            $chunkSize = $chunk->count();
            
            if ($chunkSize == 0) {
                break;
            }
            
            foreach ($chunk as $row) {
                $remoteId = $row->{$this->remoteKey};
                
                if (!$this->alreadyExists($remoteId, $row)) {
                    $data = $this->prepareData($row);
                    if ($data !== false) {
                        $localId = $this->saveData($data, $row);
                        if ($localId !== false) {
                            $this->saveRelatedData($remoteId, $localId, $row);
                            
                            if ($this->mapRefs) {
                                $this->createReference($remoteId, $localId);
                            }
                        }
                    }
                }
                
                $startFrom = $this->updateProgress($remoteId);
                
                if (!$this->command->shouldRun) {
                    return;
                }
            }
            
            if (self::$showProgress) {
                $bar->advance($chunkSize);
            }
            
            $totalDone += $chunkSize;
        }
        
        if (self::$showProgress) {
            $bar->finish();
        }
        
        $this->command->info('');
    }
    
    /**
     * Check if this record was loaded earlier
     * 
     * @param int $remoteId
     * @param object $row
     * 
     * @return bool
     */ 
    protected function alreadyExists($remoteId, $row)
    {
        if ($this->mapRefs) {
            return $this->getReference($this->srcTable, $remoteId) !== null;
        }
        return false;
    }
    
    /**
     * Migrate single record
     * 
     * @param int $remoteId
     * 
     * @return bool
     */
    public function migrateSingleRecord($remoteId)
    {
        $row = $this->getSingleRecord($remoteId);
        
        if ($row !== null) {
            $data = $this->prepareData($row);
            if ($data !== false) {
                $localId = $this->saveData($data, $row);
                if ($localId !== false) {
                    $this->saveRelatedData($remoteId, $localId, $row);
                    
                    if ($this->mapRefs) {
                        $this->createReference($remoteId, $localId);
                    }
                    
                    return $localId;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get next chunk of data
     * 
     * @param int|bool $from
     * @param int $limit
     * 
     * @return array
     */ 
    protected function getSingleRecord($remoteId)
    {
        $query = $this->getRemoteConnection()
            ->table($this->srcTable)
            ->where($this->srcTable . '.' . $this->remoteKey, $remoteId);
        
        $query = $this->customizeQuery($query);
        
        return $query->first();
    }
    
    /**
     * Run patch from this source
     * 
     * @param string $since
     */ 
    public function patch($since = null) 
    {
        if (!$this->shouldPatch) {
            return;
        }
        
        $this->command->info(sprintf('Patching data from "%s" to "%s"...', $this->getLogTable(), $this->destTable));
        
        $startFrom = $this->getLastPatchDate($since);
        $totalLeft = $this->getPatchTotalLeft($startFrom);
        $totalDone = 0;
        $totalIgnored = 0;
        $totalBreak = 0;
        
        if ($totalLeft == 0) {
            $this->command->info('Nothing new to get from there');
            return;
        }
        
        if (self::$showProgress) {
            $bar = $this->command->createProgressBar($totalLeft);
        }
        
        while ($totalDone < $totalLeft) {
            $chunk = $this->getNextChunkForPatch($startFrom, $this->chunkSize);
            $chunkSize = $chunk->count();
            
            if ($chunkSize == 0) {
                break;
            }
            
            foreach ($chunk as $row) {
                $localId = $this->getLocalIdToPatch($row);
                $logDate = Carbon::parse($row->date_insert_log);
                if ($localId) {
                    if ($this->checkPatchIsActual($localId, $logDate)) {
                        $data = $this->preparePatchData($row);
                        $remoteId = $row->{$this->remoteKey};
                        if ($data !== false) {
                            if ($this->patchData($localId, $data, $row)) {
                                $this->patchRelatedData($remoteId, $localId, $row);
                            }
                        }
                    } else {
                        $totalIgnored++;
                    }
                } else {
                    $totalBreak++;
                }
                $startFrom = $this->updatePatchProgress($logDate);
                
                if (!$this->command->shouldRun) {
                    break 2;
                }
            }
            
            if (self::$showProgress) {
                $bar->advance($chunkSize);
            }
            
            $totalDone += $chunkSize;
        }
        
        if (self::$showProgress) {
            $bar->finish();
        }
        
        $this->command->info('');
        if ($totalIgnored > 0 || $totalBreak > 0) {
            $this->command->info(sprintf('%d records were ignored, %d records have no relation', $totalIgnored, $totalBreak));
        }
    }
    
    /**
     * Patch single record
     * 
     * @param int $remoteId
     * 
     * @return bool
     */
    public function patchSingleRecord($remoteId) 
    {
        $row = $this->getSingleRecord($remoteId);
        $localId = $this->getLocalIdToPatch($row);
        
        if ($localId) {
            $data = $this->preparePatchData($row);
            if ($data !== false) {
                if ($this->patchData($localId, $data, $row)) {
                    $this->patchRelatedData($remoteId, $localId, $row);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get id of local record that should be updated
     * 
     * @param object $row
     * 
     * @return int|bool
     */ 
    protected function getLocalIdToPatch($row)
    {
        return $this->getReference($this->srcTable, $row->{$this->remoteKey});
    }
    
    /**
     * Get progress key
     * 
     * @return string
     */ 
    protected function getProgressKey()
    {
        return sprintf('data_migration_%s', $this->srcTable);
    }
    
    /**
     * Get patch key
     * 
     * @return string
     */ 
    protected function getPatchKey()
    {
        return $this->getProgressKey() . '_patch';
    }
    
    /**
     * Get log table
     * 
     * @return string
     */ 
    protected function getLogTable()
    {
        return sprintf('%sLog', $this->srcTable);
    }
    
    /**
     * Get patch date
     * 
     * @param string $min
     */ 
    protected function getLastPatchDate($min)
    {
        if (empty($min)) {
            $min = Carbon::now()->subDay();
        } else {
            $min = Carbon::parse($min);
        }
        
        $key = $this->getPatchKey();
        
        if (Redis::exists($key)) {
            $last = Carbon::parse(Redis::get($key));
            
            if ($last->greaterThan($min)) {
                return $last;
            }
        }
        
        return $min;
    }
    
    /**
     * Get progress of previous runs
     * 
     * @return int|bool
     */ 
    protected function getPreviousProgress()
    {
        $key = $this->getProgressKey();
        
        if (Redis::exists($key)) {
            return (int) Redis::get($key);
        }
        
        return false;
    }
    
    /**
     * Report current progress
     */ 
    public function reportProgress()
    {
        $lastId = (int) $this->getPreviousProgress();
        $this->command->info(sprintf('Redis::set(\'%s\', \'%d\');', $this->getProgressKey(), $lastId));
        
        if ($this->shouldPatch) {
            $date = $this->getLastPatchDate(false)->format('Y-m-d H:i:s');
            $this->command->info(sprintf('Redis::set(\'%s\', \'%s\');', $this->getPatchKey(), $date));
        }
    }
    
    /**
     * Update progress for the following runs
     * 
     * @param int $to
     * 
     * @return int
     */ 
    protected function updateProgress($to)
    {
        $key = $this->getProgressKey();
        Redis::set($key, strval($to));
        return $to;
    }
    
    /**
     * Update patch progress for the following runs
     * 
     * @param Carbon $to
     * 
     * @return Carbon
     */ 
    protected function updatePatchProgress($to)
    {
        $key = $this->getPatchKey();
        Redis::set($key, $to->format('Y-m-d H:i:s'));
        return $to;
    }
    
    /**
     * Clear progress of previous runs
     */ 
    public function clearPreviousProgress()
    {
        $key = $this->getProgressKey();
        
        if (Redis::exists($key)) {
            Redis::del([$key]);
        }
        
        $key = $this->getPatchKey();
        
        if (Redis::exists($key)) {
            Redis::del([$key]);
        }
    }
    
    /**
     * Get amount of records to migrate
     * 
     * @param int|bool $from
     * 
     * @return int
     */ 
    protected function getTotalLeft($from)
    {
        $query = $this->getRemoteConnection()
            ->table($this->srcTable);
        
        $query = $this->addRecordsFilter($query);
        
        if ($from !== false) {
            $query->where($this->remoteKey, '>', $from);
        }
        
        return $query->count();
    }
    
    /**
     * Get amount of records to patch
     * 
     * @param Carbon $from
     * 
     * @return int
     */ 
    protected function getPatchTotalLeft($from)
    {
        $query = $this->getRemoteConnection()
            ->table($this->getLogTable())
            ->where('trigger_type', 2)
            ->where('date_insert_log', '>', $from->format('Y-m-d H:i:s') . '.999');
        
        $query = $this->addPatchRecordsFilter($query);
        
        return $query->distinct($this->remoteKey)->count($this->remoteKey);
    }
    
    /**
     * Get next chunk of data
     * 
     * @param int|bool $from
     * @param int $limit
     * 
     * @return array
     */ 
    protected function getNextChunk($from, $limit)
    {
        $query = $this->getRemoteConnection()
            ->table($this->srcTable)
            ->orderBy($this->srcTable . '.' . $this->remoteKey, 'asc')
            ->limit($limit);
        
        $query = $this->customizeQuery($query);
        $query = $this->addRecordsFilter($query);
        
        if ($from !== false) {
            $query->where($this->srcTable . '.' . $this->remoteKey, '>', $from);
        }
        
        return $query->get();
    }
    
    /**
     * Get next chunk of data for patch
     * 
     * @param Carbon $from
     * @param int $limit
     * 
     * @return array
     */ 
    protected function getNextChunkForPatch($from, $limit)
    {
        $query = $this->getRemoteConnection()
            ->table($this->getLogTable())
            ->select($this->remoteKey)
            ->selectRaw('MAX(date_insert_log) as date_insert_log')
            ->orderBy('date_insert_log', 'asc')
            ->where('trigger_type', 2)
            ->where('date_insert_log', '>', $from->format('Y-m-d H:i:s') . '.999')
            ->groupBy($this->remoteKey)
            ->limit($limit);
        
        $query = $this->addPatchRecordsFilter($query);
        
        $dates = $query->get()->pluck('date_insert_log', $this->remoteKey)->all();
        $ids = array_keys($dates);
        
        $query = $this->getRemoteConnection()
            ->table($this->srcTable)
            ->whereIn($this->srcTable . '.' . $this->remoteKey, $ids);
        
        $query = $this->customizeQuery($query);
        
        $chunk = $query->get();
        foreach ($chunk as $row) {
            $row->date_insert_log = $dates[$row->{$this->remoteKey}];
        }
        
        return $chunk->sortBy(function($row) {
            return $row->date_insert_log;
        });
    }
    
    /**
     * Customize query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * 
     * @return \Illuminate\Database\Query\Builder
     */ 
    protected function customizeQuery($query)
    {
        return $query;
    }
    
    /**
     * Filter records
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * 
     * @return \Illuminate\Database\Query\Builder
     */ 
    protected function addRecordsFilter($query)
    {
        return $query;
    }
    
    /**
     * Filter patch records
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * 
     * @return \Illuminate\Database\Query\Builder
     */ 
    protected function addPatchRecordsFilter($query)
    {
        return $query;
    }
    
    /**
     * Prepare data for saving
     * 
     * @param object $data
     * 
     * @return array
     */ 
    protected function prepareData($data)
    {
        return $data;
    }
    
    /**
     * Prepare data for patching
     * 
     * @param object $data
     * 
     * @return array
     */ 
    protected function preparePatchData($data)
    {
        $prepared = $this->prepareData($data);
        unset($prepared['created_at']);
        unset($prepared['company_id']);
        unset($prepared['created_by_id']);
        return $prepared;
    }
    
    /**
     * Save data to local database
     * 
     * @param array $data
     * @param object $row
     * 
     * @return int|bool
     */ 
    protected function saveData($data, $row)
    {
        return $this->getLocalConnection()
            ->table($this->destTable)
            ->insertGetId($data);
    }
    
    /**
     * Update data in local database
     * 
     * @param int $localId
     * @param array $data
     * @param object $row
     * 
     * @return int|bool
     */ 
    protected function patchData($localId, $data, $row)
    {
        return $this->getLocalConnection()
            ->table($this->destTable)
            ->where('id', $localId)
            ->update($data);
    }
    
    /**
     * Check if there were no changes on local record
     * 
     * @param int $localId
     * @param Carbon $date
     * 
     * @return bool
     */ 
    protected function checkPatchIsActual($localId, $date)
    {
        $updated = $this->getLocalConnection()
            ->table($this->destTable)
            ->where('id', $localId)
            ->value('updated_at');
        
        return empty($updated) || $date->greaterThan($updated);
    }
    
    /**
     * Save related data
     * 
     * @param int $remoteId
     * @param int $localId
     * @param object $data
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
    }
    
    /**
     * Patch related data
     * 
     * @param int $remoteId
     * @param int $localId
     * @param object $data
     */
    protected function patchRelatedData($remoteId, $localId, $data)
    {
    }
    
    /**
     * Get data from pivot table
     * 
     * @param string $table
     * @param int $id
     * @param Closure $filter
     * 
     * @return array
     */ 
    protected function getPivotData($table, $key, $id, $filter = null)
    {
        $query = $this->getRemoteConnection()
            ->table($table)
            ->where($key, $id);
        
        if ($filter !== null) {
            $query = $filter($query);
        }
        
        return $query->get();
    }
    
    /**
     * Save pivot data
     * 
     * @param string $table
     * @param string $key
     * @paran int $id
     * @param array $data
     */ 
    protected function savePivotData($table, $key, $id, $data)
    {
        if (count($data) !== 0) {
            foreach ($data as $index => $row) {
                $data[$index][$key] = $id;
            }
            
            $this->getLocalConnection()
                ->table($table)
                ->insert($data);
        }
    }
    
    /**
     * Clear pivot data
     * 
     * @param string $table
     * @param string $key
     * @paran int $id
     */ 
    protected function clearPivotData($table, $key, $id)
    {
        $this->getLocalConnection()
            ->table($table)
            ->where($key, $id)
            ->delete();
    }
    
    /**
     * Create reference
     * 
     * @param int $remoteId
     * @param int $localId
     */ 
    protected function createReference($remoteId, $localId)
    {
        $this->getLocalConnection()
            ->table('migration_refs')
            ->insert([
                'table' => $this->srcTable,
                'remote_id' => $remoteId,
                'local_id' => $localId,
            ]);
    }
    
    /**
     * Get reference
     * 
     * @param string $table
     * @param int $remoteId
     * @param bool $cache
     * 
     * @return int
     */ 
    protected function getReference($table, $remoteId, $cache = false)
    {
        if ($remoteId === null || $remoteId === '') {
            return null;
        }
        
        if ($cache) {
            if (! array_key_exists($table, $this->refsCache)) {
                $this->loadRefsCache($table);
            }
            return $this->refsCache[$table]->has($remoteId) ? $this->refsCache[$table]->get($remoteId) : null;
        }
        
        return $this->getLocalConnection()
            ->table('migration_refs')
            ->where('table', $table)
            ->where('remote_id', $remoteId)
            ->value('local_id');
    }
    
    /**
     * Load refs cache
     * 
     * @param string $table
     */ 
    protected function loadRefsCache($table)
    {
        $this->refsCache[$table] = $this->getLocalConnection()
            ->table('migration_refs')
            ->where('table', $table)
            ->pluck('local_id', 'remote_id');
    }
    
    /**
     * Pick data
     * 
     * @param array $data
     * @param array $mapping
     * 
     * @return array
     */ 
    protected function pickData($data, $mapping, $addOwnership = true, $addTimestamps = true)
    {
        $result = [];
        
        foreach ($mapping as $to => $from) {
            if (is_array($from)) {
                list($from, $transformer) = $from;
                if (property_exists($data, $from)) {
                    $result[$to] = $transformer($data->{$from});
                }
            } else {
                if (property_exists($data, $from)) {
                    $result[$to] = $data->{$from};
                }
            }
        }
        
        if ($addOwnership) {
            $result['company_id'] = $this->companyId;
            $result['created_by_id'] = self::$createdById;
        }
        
        if ($addTimestamps) {
            $result['created_at'] = Carbon::now();
            $result['updated_at'] = Carbon::now();
        }
        
        return $result;
    }
    
    /**
     * Create data mapper for cp1251
     * 
     * @param string $column
     * @param bool $trim
     * 
     * @return array
     */ 
    protected function toUTF($column, $trim = true) 
    {
        if ($trim) {
            return [$column, function($v) {
                return $this->convertStr($v, true);
            }];
        }
        
        return [$column, function($v) {
            return $this->convertStr($v, false);
        }];
    }
    
    /**
     * Create data mapper for bool type
     * 
     * @param string $column
     * 
     * @return array
     */ 
    protected function toBool($column, $reverse = false) 
    {
        if ($reverse) {
            return [$column, function($v) {
                return $v == 1 ? 0 : 1;
            }];
        }
        
        return [$column, function($v) {
            return $v == 1 ? 1 : 0;
        }];
    }
    
    /**
     * Create data mapper for int type
     * 
     * @param string $column
     * @param bool $unsigned
     * 
     * @return array
     */ 
    protected function toInt($column, $unsigned = false) 
    {
        if ($unsigned) {
            return [$column, function($v) {
                return $v > 0 ? (int) $v : 0;
            }];
        }
        
        return [$column, function($v) {
            return (int) $v;
        }];
    }
    
    /**
     * Create data mapper for decimal type
     * 
     * @param string $column
     * @param bool $unsigned
     * 
     * @return array
     */ 
    protected function toDecimal($column, $unsigned = false) 
    {
        if ($unsigned) {
            return [$column, function($v) {
                return $v > 0 ? (float) $v : 0;
            }];
        }
        
        return [$column, function($v) {
            return (float) $v;
        }];
    }
    
    /**
     * Create data mapper for date type
     * 
     * @param string $column
     * 
     * @return array
     */ 
    protected function toDate($column)
    {
        return [$column, function($v) {
            return $v ? Carbon::parse($v)->format('Y-m-d') : null;
        }];
    }
    
    /**
     * Create data mapper for time type
     * 
     * @param string $column
     * 
     * @return array
     */ 
    protected function toTime($column)
    {
        return [$column, function($v) {
            return $v ? Carbon::parse($v)->format('H:i:s') : null;
        }];
    }
    
    /**
     * Create data mapper for reference
     * 
     * @param string $column
     * @param string $table
     * @param bool $cache
     * 
     * @return array
     */ 
    protected function fromRef($column, $table, $cache = false) 
    {
        return [$column, function($v) use($table, $cache) {
            return $this->getReference($table, $v, $cache);
        }];
    }
    
    /**
     * Create data mapper for enum
     * 
     * @param string $column
     * @param array $map
     * @param mixed $default
     * 
     * @return array
     */ 
    protected function fromMap($column, $map, $default = null) 
    {
        return [$column, function($v) use($map, $default) {
            return array_key_exists($v, $map) ? $map[$v] : $default;
        }];
    }
    
    /**
     * Get current time
     */ 
    protected function timestamp()
    {
        return Carbon::now();
    }
    
    /**
     * Check if all required data present
     * 
     * @param array $data
     * @param array $keys
     * 
     * @return bool
     */ 
    protected function checkRequired($data, $keys)
    {
        foreach ($keys as $key) {
            if (empty($data[$key])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Convert string
     * 
     * @param string $str
     * @param bool $trim
     * 
     * @return string
     */ 
    protected function convertStr($str, $trim = true)
    {
        if (self::$convertEncoding) {
            return $this->w1250Toutf8($trim ? trim($str) : $str);
        } else {
            return $trim ? trim($str) : $str;
        }
    }

    /**
     * Convert string from cp1250 to utf8
     * 
     * @param string $text
     * 
     * @return string
     */ 
    protected function w1250Toutf8($text) 
    {
        // map based on:
        // http://konfiguracja.c0.pl/iso02vscp1250en.html
        // http://konfiguracja.c0.pl/webpl/index_en.html#examp
        // http://www.htmlentities.com/html/entities/
        $map = array(
            chr(0x8A) => chr(0xA9),
            chr(0x8C) => chr(0xA6),
            chr(0x8D) => chr(0xAB),
            chr(0x8E) => chr(0xAE),
            chr(0x8F) => chr(0xAC),
            chr(0x9C) => chr(0xB6),
            chr(0x9D) => chr(0xBB),
            chr(0xA1) => chr(0xB7),
            chr(0xA5) => chr(0xA1),
            chr(0xBC) => chr(0xA5),
            chr(0x9F) => chr(0xBC),
            chr(0xB9) => chr(0xB1),
            chr(0x9A) => chr(0xB9),
            chr(0xBE) => chr(0xB5),
            chr(0x9E) => chr(0xBE),
            chr(0x80) => '&euro;',
            chr(0x82) => '&sbquo;',
            chr(0x84) => '&bdquo;',
            chr(0x85) => '&hellip;',
            chr(0x86) => '&dagger;',
            chr(0x87) => '&Dagger;',
            chr(0x89) => '&permil;',
            chr(0x8B) => '&lsaquo;',
            chr(0x91) => '&lsquo;',
            chr(0x92) => '&rsquo;',
            chr(0x93) => '&ldquo;',
            chr(0x94) => '&rdquo;',
            chr(0x95) => '&bull;',
            chr(0x96) => '&ndash;',
            chr(0x97) => '&mdash;',
            chr(0x99) => '&trade;',
            chr(0x9B) => '&rsquo;',
            chr(0xA6) => '&brvbar;',
            chr(0xA9) => '&copy;',
            chr(0xAB) => '&laquo;',
            chr(0xAE) => '&reg;',
            chr(0xB1) => '&plusmn;',
            chr(0xB5) => '&micro;',
            chr(0xB6) => '&para;',
            chr(0xB7) => '&middot;',
            chr(0xBB) => '&raquo;',
        );

        return html_entity_decode(mb_convert_encoding(strtr($text, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Reload data from remote source
     */ 
    public function forcePatch($fromId, $toId)
    {
        $query = $this->getLocalConnection()
            ->table('migration_refs')
            ->where('table', $this->srcTable);
        
        if (is_array($fromId)) {
            $query->whereIn('local_id', $fromId);
        } else {
            $query->where('local_id', '>=', $fromId)
                ->where('local_id', '<=', $toId);
        }
        
        $remoteIds = $query->pluck('remote_id', 'local_id');
        
        foreach ($remoteIds as $localId => $remoteId) {
            echo "Updating $localId <= $remoteId... \n";
            $this->patchSingleRecord($remoteId);
        }
    }
}