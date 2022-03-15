<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Models\Appointment\Service;
use App\V1\Models\Appointment\Service\Item;
use App\V1\Models\User;

class MissingIssuedMedicines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient-issued-medicines {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missing issued medicines to patient';

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
        $userId = $this->argument('user_id');
        if (!$userId) {

            return;
        }   
        $user = User::findOrFail($userId);

        $missingMedicines = \DB::table('appointment_service_items as a1')
            ->selectRaw('a1.appointment_service_id,a1.service_type,a1.quantity as medicine_quantity, a2.*,a3.id as assigned_medicine_id,a4.id as document_id')
            ->join('appointment_services as a2', 'a1.appointment_service_id', 'a2.id')
            ->join('assigned_medicines as a3', 'a3.id', 'a1.service_id')
            ->leftJoin('issued_medicine_documents as a4', 'a4.appointment_service_id', 'a1.appointment_service_id')
            ->where('a2.container_type', '=', Service::CONTAINER_MEDICINES)
            ->where('a1.service_type', '=', Item::ASSIGNED_MEDICINE)
            ->where('a2.issued', '=', 1)
            ->orderBy('a3.id','desc')
            ->whereNotExists(function($query)
                {
                    $query->select('issued_medicines.id')
                          ->from('issued_medicines')
                          ->whereRaw('issued_medicines.assigned_medicine_id = a3.id');
                })
           ->get();
        
        if($missingMedicines->count() === 0) {
            $this->info('No missing issued medicines find');
        }else{
            $this->info('Start adding issued medicines');
            foreach ($missingMedicines as $value) {
                \DB::table('issued_medicines')
                        ->insert([
                            'assigned_medicine_id' => $value->assigned_medicine_id,
                            'quantity' => $value->medicine_quantity,
                            'medicine_document_id' => $value->document_id,
                            'issued_by_id' => $user->userable_id,
                            'created_at' => now()
                        ]);
                $this->info('Added issued medicine');                      
            }
        }
    }
}
