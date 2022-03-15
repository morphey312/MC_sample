<?php

namespace App\V1\Console\Commands\OneS;

use App\V1\Models\Medicine;
use Http\Client\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshMedicines extends Command
{
    use Import;

    const COMMAND = 'get_firms';

    const ACCORDANCE_COMMAND = 'get_accordance';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one_s:refresh_medicines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh medicines id and description from 1C';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accordance = $this->getAccordance()['data']['result'];

        foreach ($accordance as $medication) {
            $medicine = Medicine::where('medicine_uid', $medication['id'])->first();

            if ($medicine && $medication['id_new'] !== '00000000-0000-0000-0000-000000000000') {
                $medicine->new_medicine_uid = $medication['id_new'];
                $medicine->new_description_full = $medication['description_new'];
                $medicine->save();
            }
        }
    }

    private function getAccordance() {
        return $this->client->sendImportCommand(self::ACCORDANCE_COMMAND);
    }
}
