<?php

namespace App\V1\Console\Commands\Elastic;

use Illuminate\Console\Command;
use App\V1\Jobs\Elastic\Report\CallCenter\BatchSessionLogJob;

class CallCenterSessionLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic-export:call_center_session_logs {clinic?} {--date_from=} {--date_to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send session log data for operator bonuses reports';

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
        $dateFrom = $this->option('date_from');
        $dateTo = $this->option('date_to');
        
        if (empty($dateFrom)) {
            $this->info("Input date_from option");
            return;
        }

        if (empty($dateTo)) {
            $this->info("Input date_to option");
            return;
        }
        BatchSessionLogJob::dispatch($dateFrom, $dateTo)->onQueue('elastic');
    }
}
