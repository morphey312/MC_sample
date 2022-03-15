<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;

class UniquePatientIssuedCardOwner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient-issued-card:unique';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unique Patient Issued card owner';

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
        $duplicatedOwner = \DB::table('patient_issued_cards')
            ->selectRaw('issued_card_id,GROUP_CONCAT(patient_id) as duplicated, SUM(is_owner) as counter')
            ->where('is_owner', '=', 1)
            ->groupBy('issued_card_id')
            ->havingRaw('counter > 1')
            ->get();

        if($duplicatedOwner->count() === 0) {
            $this->info('No duplicate Card owners found');
        }else{
            $this->info('Start owner deactivation');
            foreach ($duplicatedOwner as $value) {
                $patients = explode(',',$value->duplicated);
                array_shift($patients);
                \DB::table('patient_issued_cards')
                    ->where('issued_card_id','=',$value->issued_card_id)
                    ->whereIn('patient_id',$patients)
                    ->update(['is_owner' => 0]);
                $this->info('Disabled owner for ' . implode(',',$patients));

            }
        }

    }

}
