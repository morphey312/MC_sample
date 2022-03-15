<?php

namespace App\V1\Console\Commands\OneS\Import;

use Illuminate\Console\Command;
use App\V1\Models\Medicine;
use App\V1\Console\Commands\OneS\Import;

class Medicines extends Command
{
    use Import;

    const COMMAND = 'get_items';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:medicines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import medicines from 1c';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->write('Started at - ' . now());
        $this->write('Receiving medicine list...');
        $results = $this->getData();

        if (isset($results['error'])) {
            $this->write($results['error'], true);
            return $this->abort();
        }

        $this->saveResults($results['data']['result']);
        $this->write('Store list import finished');
    }

    /**
     * Save medicine
     *
     * @param array $attributes
     *
     * @return object
     */
    protected function save($attributes)
    {
        $medicine = Medicine::firstOrNew(['new_medicine_uid' => $attributes['id']]);
        $medicine->medicine_uid = $attributes['id'];
        $medicine->parent_uid = $attributes['parent'];
        $medicine->code = $attributes['code'];
        $medicine->name = empty($attributes['description_ukr']) ? $attributes['description'] : $attributes['description_ukr'];
        $medicine->name_lc1 = $attributes['description'];
        $medicine->description_full = $attributes['description_full'];
        $medicine->new_description_full = $attributes['description_full'];
        $medicine->measure = $attributes['measure'];
        $medicine->type = $attributes['type'];
        $medicine->articul = $attributes['articul'];

        return $medicine->save();
    }
}
