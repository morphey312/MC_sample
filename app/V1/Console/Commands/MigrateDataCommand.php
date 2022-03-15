<?php

/**
 * TODO:
 * + fix patient phone number format
 * - ???!!! const ANALYSIS_NAME = 'Analýzy';
 * + Procedural cabinet 
 */

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Redis;
use Exception;

class MigrateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:migrate {--show-progress} {--continue} {--convert-encoding} {--progress-bar} {--patch-since=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from sucks place to cool place';
    
    /**
     * @var bool
     */ 
    public $shouldRun = true;
    
    /**
     * @var array
     */ 
    protected $sources = [
        MigrateData\Clinics::class,
        MigrateData\Positions::class, 
        //  MigrateData\Specializations::class, // shared
        //  MigrateData\AdjacentSpecializations::class, // shared
        MigrateData\ClinicSpecializations::class,
        MigrateData\Employees::class,
        // MigrateData\Cabinets::class,
        MigrateData\EmployeeClinics::class,
        MigrateData\DaySheets::class,
        // MigrateData\MediaTypes::class, // shared
        MigrateData\InformationSources::class,
        MigrateData\BlackMarkReasons::class,
        MigrateData\SkkRequestReasons::class,
        MigrateData\Patients::class,
        // MigrateData\AppointmentStatuses::class, // shared
        // MigrateData\PaymentTypes::class, // shared
        // MigrateData\PaymentDestinations::class, // shared
        // MigrateData\DiscountCardNumerations::class,
        // MigrateData\DiscountCardTypes::class,
        // MigrateData\PatientRelations::class,
        // MigrateData\PatientDiscountCards::class,
        MigrateData\PatientCards::class,
        MigrateData\Services::class,
        MigrateData\Analyses::class,
        MigrateData\ServicePrices::class,
        MigrateData\AnalysisPrices::class,
        MigrateData\AppointmentDeleteReasons::class,
        MigrateData\AppointmentStatusReasons::class,
        MigrateData\TreatmentCourses::class,
        MigrateData\TreatmentRejectReasons::class,
        MigrateData\Appointments::class,
        MigrateData\AppointmentServices::class,
        MigrateData\AppointmentAnalyses::class,
        MigrateData\CallResults::class,
        MigrateData\CallDeleteReasons::class,
        MigrateData\CallRequestPurposes::class,
        MigrateData\CallRequests::class,
        MigrateData\Calls::class,
        MigrateData\NotProcessedCallReasons::class,
        MigrateData\CallProcess::class,
        MigrateData\Payments::class,
        /*
        MigrateData\DumbPayments::class,
        */
        
        
        // MigrateData\Roles::class,
        // MigrateData\PatientContacts::class,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setupSignals();
        
        $continue = $this->option('continue');
        $patchSince = $this->option('patch-since');
        $showProgress = $this->option('show-progress');
        
        MigrateData\BaseMigrate::$showProgress = $this->option('progress-bar');
        MigrateData\BaseMigrate::$convertEncoding = $this->option('convert-encoding');

        if ($showProgress) {
            foreach ($this->sources as $source) {
                $worker = new $source($this);
                $worker->reportProgress();
            }
            return;
        }
        
        if (!$continue) {
            if (!$this->confirm('You are about to drop ALL progress, are you sure?')) {
                return;
            }
            
            DB::table('migration_refs')->truncate();
            
            foreach ($this->sources as $source) {
                $worker = new $source($this);
                $worker->clearPreviousProgress();
            }

            $this->setupRefs();
        }

        // (new MigrateData\CallRequests($this))->clearPreviousProgress();
        
        try {
            $this->info('Migration started');
            foreach ($this->sources as $source) {
                $worker = new $source($this);
                
                $worker->setup();
                $worker->migrate($continue);
                
                if (!$this->shouldRun) {
                    $worker->shutdown();
                    break;
                }
                
                $worker->patch($patchSince);
                $worker->shutdown();
                
                if (!$this->shouldRun) {
                    break;
                }
            }
            
            if ($this->shouldRun) {
                $this->info('Migration completed!');
            } else {
                $this->info('Migration terminated. Use --continue while next run to not lose progress.');
            }
        } catch (Exception $e) {
            $this->info('Migration error:' . $e->getMessage());
        }
    }

    /**
     * Setup references
     */
    protected function setupRefs()
    {
        DB::table('migration_refs')->insert([
            // Specializations
            ['table' => 'list_specialization', 'remote_id' => 3, 'local_id' => 4], // Proktológia
            ['table' => 'list_specialization', 'remote_id' => 20, 'local_id' => 12], // Analýzy
            ['table' => 'list_specialization', 'remote_id' => 21, 'local_id' => 13], // mimo špecializácie
            ['table' => 'list_specialization', 'remote_id' => 23, 'local_id' => 6], // Dermatologia
            ['table' => 'list_specialization', 'remote_id' => 24, 'local_id' => 11], // Trichologia
            ['table' => 'list_specialization', 'remote_id' => 25, 'local_id' => 54], // CERTIFIKÁT
            ['table' => 'list_specialization', 'remote_id' => 26, 'local_id' => 3], // USG
            
            // Payment methods
            ['table' => 'list_type_pay', 'remote_id' => 3, 'local_id' => 2], // Kreditnou kartou (bankový prevod)
            ['table' => 'list_type_pay', 'remote_id' => 4, 'local_id' => 3], // Hotovosť
            ['table' => 'list_type_pay', 'remote_id' => 5, 'local_id' => 5], // Bežný účet
            ['table' => 'list_type_pay', 'remote_id' => 6, 'local_id' => 6], // CERTIFIKÁT

            // Payment destinations
            ['table' => 'list_purpose_pay', 'remote_id' => 1, 'local_id' => 2], // Analýzy
            ['table' => 'list_purpose_pay', 'remote_id' => 2, 'local_id' => 3], // Konzultácia
            ['table' => 'list_purpose_pay', 'remote_id' => 3, 'local_id' => 4], // Liečenie
            ['table' => 'list_purpose_pay', 'remote_id' => 4, 'local_id' => 11], // CERTIFIKÁT
            ['table' => 'list_purpose_pay', 'remote_id' => 5, 'local_id' => 5], // USG

            // Appointment statuses
            ['table' => 'list_status', 'remote_id' => 0, 'local_id' => 1], // Pacient sa zapísal na vyšetrenie
            ['table' => 'list_status', 'remote_id' => 1, 'local_id' => 2], // Pacient prišiel na recepciu
            ['table' => 'list_status', 'remote_id' => 2, 'local_id' => 3], // Pacient išiel k lekárovi
            ['table' => 'list_status', 'remote_id' => 3, 'local_id' => 4], // Pacient neprišiel na vyšetrenie
            ['table' => 'list_status', 'remote_id' => 4, 'local_id' => 5], // Analýzy spravené
            ['table' => 'list_status', 'remote_id' => 5, 'local_id' => 6], // Analýzy sú hotové
            ['table' => 'list_status', 'remote_id' => 6, 'local_id' => 7], // Analýzy sú odoslané
            ['table' => 'list_status', 'remote_id' => 7, 'local_id' => 9], // Nie všetky analýzy sú odovzdané
            ['table' => 'list_status', 'remote_id' => 8, 'local_id' => 8], // Platba

            // Media types
            ['table' => 'list_TypeReklama', 'remote_id' => 1, 'local_id' => 140], // Iné
            ['table' => 'list_TypeReklama', 'remote_id' => 2, 'local_id' => 135], // Print
            ['table' => 'list_TypeReklama', 'remote_id' => 3, 'local_id' => 136], // Outdoor
            ['table' => 'list_TypeReklama', 'remote_id' => 4, 'local_id' => 138], // Radio
            ['table' => 'list_TypeReklama', 'remote_id' => 5, 'local_id' => 139], // Internet
            ['table' => 'list_TypeReklama', 'remote_id' => 6, 'local_id' => 140], // Others
            ['table' => 'list_TypeReklama', 'remote_id' => 7, 'local_id' => 141], // Referencie
            ['table' => 'list_TypeReklama', 'remote_id' => 8, 'local_id' => 137], // TV
        ]);
    }
    
    /**
     * Create a progress bar
     * 
     * @param int $count
     * 
     * @return \Symfony\Component\Console\Style\ProgressBar
     */ 
    public function createProgressBar($count)
    {
        return $this->output->createProgressBar($count);
    }
    
    /**
     * Setup pcntl signals
     */ 
    protected function setupSignals()
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function () {
                $this->shouldRun = false;
            });
        }
    }
}
