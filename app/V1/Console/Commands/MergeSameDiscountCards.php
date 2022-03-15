<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\V1\Models\Appointment;
use App\V1\Models\Patient\IssuedDiscountCard;
use App\V1\Contracts\Repositories\Patient\IssuedDiscountCardRepository;

class MergeSameDiscountCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge:discount_cards';

    /**
     * @var
     */
    protected $repository;

    /**
     * List of relations to merge
     * 
     * @var array
     */
    protected $relations = [
        'appointments',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge patient discount cards in same clinic with same number';


    public function __construct(IssuedDiscountCardRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $numbers = DB::table('issued_discount_cards')
            ->selectRaw('number, clinic_id, COUNT(id) AS cards, discount_card_type_id, GROUP_CONCAT(id) as ids')
            ->groupBy('clinic_id', 'discount_card_type_id', 'number')
            ->havingRaw('cards > 1')
            ->get();

        if ($numbers->isEmpty()) {
            $this->info("List is empty");
        }

        $this->info("Merge starting...");
        $this->info($numbers->count() . " groups found");

        foreach ($numbers as $cardGroup) {
            $cards = explode(',', $cardGroup->ids);
            $destination = $this->repository->find($cards[0]);
            if ($destination === null) {
                continue;
            }
            $this->info("Destination id - "  . $destination->id);

            $this->setSingleOwner($destination);

            foreach ($cards as $index => $id) {
                if ($index === 0) {
                    continue;
                }
                
                $source = $this->repository->find($id);
                
                if ($source === null) {
                    $this->warn("Source card not found - "  . $id);
                    continue;
                }

                $this->info("Merging source - "  . $id);
                try {
                    $destination->reparent($source, $this->relations);
                    $this->mergePatients($destination->id, $id);
                    $this->info("Deleting source - "  . $id);
                    $source->delete();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info("Merge finished...");
    }

    protected function setSingleOwner($destination)
    {
        $owners = $destination->patients->filter(function($patient) {
            return $patient->pivot->is_owner === 1;
        });

        if ($owners->count() > 1) {
            $id = $owners->pluck('id')->all();
            array_shift($id);
            $this->getBaseQuery()
                ->whereIn('patient_id', $id)
                ->where('issued_card_id', '=', $destination->id)
                ->update([
                    'is_owner' => 0,
                ]);
        }
    }

    /**
     * 
     */
    protected function mergePatients($destinationId, $sourceId)
    {
        $this->info("Merging patients, source - "  . $sourceId);
        $this->getBaseQuery()
            ->where('issued_card_id', '=', $sourceId)
            ->update([
                'issued_card_id' => $destinationId,
                'is_owner' => 0,
            ]);
    }


    protected function getBaseQuery()
    {
        return DB::table('patient_issued_cards');
    }
}