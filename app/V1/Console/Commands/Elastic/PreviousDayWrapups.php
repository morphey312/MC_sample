<?php

namespace App\V1\Console\Commands\Elastic;

use Illuminate\Console\Command;
use App\V1\Jobs\Elastic\Report\CallCenter\BatchSessionLogJob;
use Carbon\Carbon;

class PreviousDayWrapups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic-export:previous_day_wrapups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send operators wrapups data to elastic';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prevDay = Carbon::now()->subDay()->format('Y-m-d');
        BatchSessionLogJob::dispatch($prevDay, $prevDay)->onQueue('elastic');
    }
}