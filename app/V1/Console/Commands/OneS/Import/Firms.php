<?php

namespace App\V1\Console\Commands\OneS\Import;

use App\V1\Models\MedicineFirm;
use Illuminate\Console\Command;
use App\V1\Console\Commands\OneS\Import;

class Firms extends Command
{
    use Import;

    const COMMAND = 'get_firms';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:firms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import medicine firms from 1c';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->write('Started at - ' . now());
        $this->write('Receiving firm list');
        $results = $this->getData();

        if (isset($results['error'])) {
            $this->write($results['error'], true);
            return $this->abort();
        }

        $this->write('Start import firm list');
        $this->saveResults($results['data']['result']);
        $this->write('Firm list import finished');
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
        $store = MedicineFirm::firstOrNew(['firm_uid' => $attributes['id']]);
        $store->parent = $attributes['parent'];
        $store->code = $attributes['code'];
        $store->description = $attributes['description'];
        $store->is_group = $attributes['is_group'];

        return $store->save();
    }
}
