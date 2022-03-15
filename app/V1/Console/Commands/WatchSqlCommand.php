<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;

class WatchSqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:sql {--interval=60}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch sql query count';
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $intervalOpt = $this->option('interval');
        if ($intervalOpt === 'manual') {
            $interval = -1;
        } else {
            $interval = max(5, (int) $intervalOpt);
        }

        $stats = $this->getStats();
        
        $this->info('Starting to gather stats...');
        
        if ($interval === -1) {
            $this->info('Hit Enter to update stats...');
        }
        
        $this->info('| time|select|insert|update|delete|');
        
        while (true) {
            if ($interval === -1) {
                $h = fopen("php://stdin","r");
                fgetc($h);
                fclose($h);
            } else {
                sleep($interval);
            }
            $new = $this->getStats();
            $delta = $this->getDelta($stats, $new);
            $stats = $new;
            $this->info(sprintf('|%s|%6d|%6d|%6d|%6d|',
                Carbon::now()->format('H:i'),
                $delta['Com_select'], 
                $delta['Com_insert'], 
                $delta['Com_update'], 
                $delta['Com_delete']));
        }
    }
    
    /**
     * Calculate delta
     * 
     * @param array $prev
     * @param array $next
     * 
     * @return array
     */ 
    protected function getDelta($prev, $next)
    {
        $delta = [];
        foreach ($prev as $key => $val) {
            $delta[$key] = $next[$key] - $prev[$key];
        }
        return $delta;
    }
    
    /**
     * Get current stats
     * 
     * @return array
     */ 
    protected function getStats()
    {
        $data = DB::select("show global status where Variable_name in ('Com_select', 'Com_insert', 'Com_update', 'Com_delete')");
        $stats = [];
        foreach ($data as $row) {
            $stats[$row->Variable_name] = $row->Value;
        }
        return $stats;
    }
}
