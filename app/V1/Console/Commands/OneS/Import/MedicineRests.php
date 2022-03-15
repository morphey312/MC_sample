<?php

namespace App\V1\Console\Commands\OneS\Import;

use App\V1\Models\Clinic;
use App\V1\Models\MedicineFirm;
use Illuminate\Console\Command;
use App\V1\Models\Medicine;
use App\V1\Models\MedicineStore;
use App\V1\Console\Commands\OneS\Import;
use Exception;
use Illuminate\Support\Facades\DB;

class MedicineRests extends Command
{
    use Import;

    const COMMAND = 'get_rests';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:medicine_rests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import medicine rests by related medicine stores';

    /**
     * All medicine stores collection
     *
     * @var
     */
    protected $stores;

    /**
     * All medicine stores collection
     *
     * @var
     */
    protected $firms;

    /**
     * Rests keyed by store uid
     *
     * @var
     */
    protected $rests = [];

    /**
     * All medicines collection
     *
     * @var
     */
    protected $medicines;

    /**
     * @var array
     */
    protected $medicineFirmClinics = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->write('Started at - ' . now());
        $this->write('Receiving medicine stores rests...');
        $results = $this->getData();

        if (isset($results['error'])) {
            $this->write($results['error'], true);
            return $this->abort();
        }

        $this->medicineFirmClinics = $this->getMedicineFirmClinics();
        $this->getStores();
        $this->getFirms();
        $this->getStoreRests($results['data']['result']);
        $this->getFirmRests();
        $this->getMedicines();
        $this->dropRests();
        $this->attachRests();
        $this->write('Store rests import finished');
    }

    /**
     * Get clinics by medicine firm id
     *
     * @return array
     */
    private function getMedicineFirmClinics(): array
    {
        $medicineFirmClinics = [];

        $clinics = Clinic::whereNotNull('medicine_firm_id')->get();

        foreach ($clinics as $clinic) {
            if (array_key_exists($clinic->medicine_firm_id, $medicineFirmClinics)) {
                $medicineFirmClinics[$clinic->medicine_firm_id] = array_merge($medicineFirmClinics[$clinic->medicine_firm_id], [$clinic->id]);
            } else {
                $medicineFirmClinics[$clinic->medicine_firm_id] = [$clinic->id];
            }
        }

        return $medicineFirmClinics;
    }

    /**
     * Get rests key by stores
     *
     * @param array $results
     */
    protected function getStoreRests($results)
    {
        foreach ($this->getRow($this->stores) as $store) {
            $uid = $store['store_uid'];
            $this->rests[$uid] = array_filter($results, function ($row) use ($uid) {
                return $row['store'] === $uid;
            });
        }
    }

    /**
     * Add firm_id to rests
     *
     */
    protected function getFirmRests()
    {
        foreach ($this->rests as $store => $rest) {
            if ($rest) {
                foreach ($rest as $restKey => $item) {
                    $this->rests[$store][$restKey]['firm_id'] = $this->firms->where('firm_uid', $item['firm'])->first()->id;
                }
            }
        }
    }

    /**
     * Fetch all medicine stores
     */
    protected function getStores()
    {
        $this->stores = MedicineStore::select('id', 'store_uid', 'description')->get();
    }

    /**
     * Fetch all medicine firms
     */
    protected function getFirms()
    {
        $this->firms = MedicineFirm::select('id', 'firm_uid', 'description')->get();
    }

    /**
     * Fetch all medicines
     */
    protected function getMedicines()
    {
        $this->medicines = Medicine::pluck('id', 'new_medicine_uid');
    }

    /**
     * Store recieved rests
     */
    protected function attachRests()
    {
        $bar = $this->output->createProgressBar(count($this->medicines));
        $bar->start();

        foreach ($this->getRow($this->rests, true) as $uid => $rest) {
            if (empty($rest)) {
                continue;
            }

            $store = $this->stores->where('store_uid', $uid)->first();

            if ($this->updateRests($store, $rest)) {
                $bar->advance();
            } else {
                $bar->finish();
                $this->write('There is an error on updating "' . $store['description'], true);
                $this->abort(1);
            }
        }

        $bar->finish();
    }

    /**
     * Attach single medicine store rests
     *
     * @param $store
     * @param $rests
     *
     * @return bool
     */
    protected function updateRests($store, $rests)
    {
        foreach ($this->getRow($rests) as $row) {
            if ($id = $this->findMedicineId($row['item'])) {
                $medicineFirmClinics = array_key_exists($row['firm_id'], $this->medicineFirmClinics) ? $this->medicineFirmClinics[$row['firm_id']] : null;

                if ($medicineFirmClinics) {
                    foreach ($medicineFirmClinics as $medicineFirmClinic) {
                        try {
                            DB::table('medicine_store_rests')->insert([
                                'store_id' => $store->id,
                                'medicine_id' => $id,
                                'rest' => $row['rest'],
                                'cost' => $this->formatCost($row['sell_price'], 0),
                                'self_cost' => $this->formatCost($row['cost_price']),
                                'firm_id' => $row['firm_id'],
                                'clinic_id' => $medicineFirmClinic,
                            ]);
                        } catch (Exception $e) {
                            $this->write($e->getMessage(), true);
                            return $this->abort();
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Find medicine id in retrived medicine collection
     *
     * @param string medicine $uid
     *
     * @return int|null
     */
    protected function findMedicineId($uid)
    {
        return $this->medicines[$uid] ?? null;
    }

    /**
     * Format to price value
     *
     * @param $number
     *
     * @return float
     */
    public function formatCost($number, $precision = 2)
    {
        return round($number, $precision);
    }

    /**
     * Drop rests
     */
    protected function dropRests()
    {
        DB::table('medicine_store_rests')->delete();
    }
}
