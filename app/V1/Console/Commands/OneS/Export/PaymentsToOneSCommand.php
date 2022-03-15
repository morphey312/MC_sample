<?php

namespace App\V1\Console\Commands\OneS\Export;

use Illuminate\Console\Command;
use App\V1\Models\Payment;
use App\V1\Jobs\SendOneSTransactions;

class PaymentsToOneSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:payments_to_one_s {ids*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export payments by ids';

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
        $ids = $this->argument('ids');

        if (empty($ids)) {
            $this->info("Input payments ids");
            return;
        }

        Payment::whereIn('id', $ids)
                ->orderBy('id')
                ->chunk(1, function($payments) {
                    SendOneSTransactions::dispatch($payments->all());
                });
    }
}