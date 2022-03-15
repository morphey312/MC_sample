<?php

namespace App\V1\Console\Commands\OneS\Export;

use Illuminate\Console\Command;
use App\V1\Models\Payment;
use App\V1\Jobs\SendOneSTransactions;

class PeriodPaymentsToOneSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:period_payments_to_one_s {clinics*} {--date_from=} {--date_to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export payments from clinics by single date';

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

        Payment::whereIn('clinic_id', $clinics)
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->whereNotNull('created_by_id')
                ->orderBy('id')
                ->chunk(1, function($payments) {
                    SendOneSTransactions::dispatch($payments->all());
                });
    }
}