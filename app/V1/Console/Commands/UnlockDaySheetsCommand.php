<?php

namespace App\V1\Console\Commands;

use App\V1\Http\Resources\DaySheet\LockResource;
use Illuminate\Console\Command;
use App\V1\Models\DaySheet;
use App\V1\Events\Broadcast\DaySheetLock;
use Carbon\Carbon;

class UnlockDaySheetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unlock:day-sheets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock delayed day sheets locks';

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
        $now = Carbon::now()->subMinutes(DaySheet::UNLOCK_DELAY)->toDateTimeString();
        $locks = DaySheet\Lock::with('day_sheet')
            ->where('type', '!=', 'fixed')
            ->where("created_at", "<", $now)->get();

        if ($locks->isEmpty()) {
            return;
        }

        $daySheets = $locks->pluck('day_sheet');

        $locks->each(function($lock) {
            $lock->delete();
        });

        $daySheets->each(function($daySheet) {
            broadcast(new DaySheetLock([
                $daySheet->id => new LockResource($daySheet->fresh()->locks)
            ]));
        });
    }
}
