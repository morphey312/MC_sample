<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Exception;

class TinyMssql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mssql:cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run mssql queries';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ignoreCols = [
            'copy_from_server',
            'copy_row_guide',
            'copy_is_snapshoted',
        ];
        
        while (true) {
            $cmd = $this->ask('Query');
            if ($cmd == 'q') break;
            
            if (preg_match('#^t (.+)#', $cmd, $matches)) {
                $cmd = sprintf('select table_name from information_schema.tables where table_name like \'%%%s%%\' order by table_name', $matches[1]);
            } elseif ($cmd == 't') {
                $cmd = 'select table_name from information_schema.tables order by table_name';
            } elseif (preg_match('#^f (.+)#', $cmd, $matches)) {
                $cmd = sprintf('select column_name, data_type, character_maximum_length from information_schema.columns where table_name = \'%s\'', $matches[1]);
            } elseif (preg_match('#^s (.+) (\d+) (.+)#', $cmd, $matches)) {
                $cmd = sprintf('select top %d %s from [%s]', $matches[2], $matches[3], $matches[1]);
            } elseif (preg_match('#^s (.+) (\d+)#', $cmd, $matches)) {
                $cmd = sprintf('select top %d * from [%s]', $matches[2], $matches[1]);
            } elseif (preg_match('#^s (.+)#', $cmd, $matches)) {
                $cmd = sprintf('select top 100 * from [%s]', $matches[1]);
            }
            
            try {
                $cmd = mb_convert_encoding($cmd, 'cp1251', 'utf8');
                $result = DB::connection('sqlsrv')->select($cmd);
                
                if (count($result) !== 0) {
                    $headers = array_diff(array_keys((array) $result[0]), $ignoreCols);
                    $this->table($headers, array_map(function($row) use($ignoreCols) {
                        $out = [];
                        foreach ((array) $row as $f => $field) {
                            if (!in_array($f, $ignoreCols)) {
                                $out[] = mb_convert_encoding($field, 'utf8', 'cp1251');
                            }
                        }
                        return $out;
                    }, $result));
                } else {
                    $this->info('Empty result');
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
