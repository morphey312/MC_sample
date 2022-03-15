<?php

namespace App\V1\Console\Commands\Elastic;

use Illuminate\Console\Command;
use App\V1\Jobs\Elastic\Report\Redirects\BatchAppointmentJob;
use App\V1\Models\Clinic;
use Carbon\Carbon;

class RedirectsAppointmentAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic-export:redirects_all';

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
        $clinics = Clinic::all();
        $today = Carbon::now()->subDay();
        $dateTo = $today->format('Y-m-d');
        $dateFrom = $today->startOfMonth()->format('Y-m-d');
        $counter = 0;

        foreach($clinics->chunk(1) as $chunk) {
            foreach ($chunk as $clinic) {
                if ($counter === 0) {
                    BatchAppointmentJob::dispatch([$clinic->id], $dateFrom, $dateTo)->onQueue('elastic');
                } else {
                    BatchAppointmentJob::dispatch([$clinic->id], $dateFrom, $dateTo)->onQueue('elastic')
                        ->delay(Carbon::now()->addMinutes(3 * $counter));
                }
            }
            $counter++;
        }
    }
}
