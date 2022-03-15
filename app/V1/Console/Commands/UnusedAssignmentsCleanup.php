<?php

namespace App\V1\Console\Commands;

use App\V1\Models\Analysis\Result;
use App\V1\Models\Patient;
use App\V1\Models\Patient\AssignedService;
use DB;
use Illuminate\Console\Command;

class UnusedAssignmentsCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup unused assignments (default 180 days)';

    private $date;

    protected function log($text)
    {
        \Log::debug($text);
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->date = now()->subDays(Patient::ASSIGNMENTS_MAX_LIFE_IN_DAYS)
        ->setTime(0, 0, 0)->toDateTimeString();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->analysisCleanup();

        $this->servicesCleanup();
    }

    /*
     * Cleanup old analysis
     * */
    protected function analysisCleanup(): void
    {
        $this->log('Analyses cleanup BEGIN');

        $analyses = Result::where('status', '=', Result::STATUS_ASSIGNED)
            ->where('created_at', '<', $this->date)
            ->whereNotIn('id', function($query) {
                $query->select('service_id')
                    ->from('appointment_service_items')
                    ->where('service_type', '=', 'analysis_result');
            })->get();

        $this->log("Found " . count($analyses) . ' assigned analyses, cleaning up');

        if ($analyses->isNotEmpty()) {
            \DB::table('analysis_results')->whereIn('id', $analyses->pluck('id'))
                ->update(['is_archived' => 1]);
        }

        $this->log("Cleaned up analyses");
    }

    /*
     * Cleanup old services
     * */
    protected function servicesCleanup(): void
    {
        $this->log('Services cleanup BEGIN');

        $services = AssignedService::where('quantity', '>', 0)
            ->where('created_at', '<', $this->date)->get();

        $this->log("Found " . count($services) . ' assigned analyses, cleaning up');

        if ($services->isNotEmpty()) {
            \DB::table('assigned_services')->whereIn('id', $services->pluck('id'))
                ->update(['is_archived' => 1]);
        }

        $this->log("Services cleaned");
    }
}
