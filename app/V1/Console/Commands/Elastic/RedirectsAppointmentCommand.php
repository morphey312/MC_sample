<?php

namespace App\V1\Console\Commands\Elastic;

use Illuminate\Console\Command;
use App\V1\Jobs\Elastic\Report\Redirects\BatchAppointmentJob;

class RedirectsAppointmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic-export:redirects_appointments {clinics*} {--date_from=} {--date_to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointments data for redirects report to elastic';

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
        $clinics = $this->argument('clinics');
        $dateFrom = $this->option('date_from');
        $dateTo = $this->option('date_to');
        
        if (empty($clinics)) {
            $this->info("Input clinic ids");
            return;
        }

        if (empty($dateFrom)) {
            $this->info("Input date_from option");
            return;
        }

        if (empty($dateTo)) {
            $this->info("Input date_to option");
            return;
        }
        BatchAppointmentJob::dispatch($clinics, $dateFrom, $dateTo)->onQueue('elastic');
    }
}
