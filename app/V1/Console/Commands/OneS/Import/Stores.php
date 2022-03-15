<?php

namespace App\V1\Console\Commands\OneS\Import;

use Illuminate\Console\Command;
use App\V1\Models\MedicineStore;
use App\V1\Console\Commands\OneS\Import;

class Stores extends Command
{
    use Import;

    const COMMAND = 'get_stores';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import medicine stores from 1c';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->write('Started at - ' . now());
        $this->write('Receiving store list');
        $results = $this->getData();

        if (isset($results['error'])) {
            $this->write($results['error'], true);
            return $this->abort();
        }

        $this->write('Start import store list');
        $this->saveResults($results['data']['result']);
        $this->write('Store list import finished');
    }

    /**
     * Save store
     *
     * @param array $attributes
     *
     * @return object
     */
    protected function save($attributes)
    {
        $store = MedicineStore::firstOrNew(['store_uid' => $attributes['id']]);
        $store->code = $attributes['code'];
        $store->description = $attributes['description'];
        return $store->save();
    }
}
