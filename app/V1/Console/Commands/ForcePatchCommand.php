<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Redis;
use Exception;

class ForcePatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:force-patch {class} {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reload data from remote source';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('class');
        $fqcn = 'App\V1\Console\Commands\MigrateData\\' . $class;
        $src = new $fqcn($this);
        $idsArg = $this->argument('ids');
        
        if (strpos($idsArg, ',') !== false) {
            $fromId = explode(',', $idsArg);
            $toId = null;
        } else {
            $ids = explode('-', $idsArg);
            if (count($ids) == 1) {
                $fromId = $toId = $ids[0];
            } else {
                $fromId = $ids[0];
                $toId = $ids[1];
            }
        }
        
        $src->setup();
        $src->forcePatch($fromId, $toId);
    }
}
