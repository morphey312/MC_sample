<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Redis;
use Exception;

class LoadMissingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:load-missing {class} {ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load missing data from remote source';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = $this->argument('class');
        $fqcn = 'App\V1\Console\Commands\MigrateData\\' . $class;
        $ids = explode(',', $this->argument('ids'));
        $src = new $fqcn($this);
        
        $src->setup();
        foreach($ids as $id) {
            $src->migrateSingleRecord($id);
        }
    }
}
