<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\V1\Models\Price;
use Auth;
use App\V1\Models\User;

class CloseInsurancePriceCommand extends Command
{
    const BASE_SET_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:close-insurance {--interactive} {--user=admin} {--service=} {--analysis=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close insurance prices';

    /**
     * @var bool
     */
    protected $interactive;

    /**
     * @var int
     */
    protected $chunkNumber = 1;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->interactive = $this->option('interactive');
        $username = $this->option('user');
        $service = $this->option('service');
        $analysis = $this->option('analysis');
        $user = User::where('login', $username)->first();

        if ($user === null) {
            $this->error("Unable to authorize with '$username'");
            return;
        }

        Auth::login($user);

        Price::where('set_id', '!=', self::BASE_SET_ID)
            ->whereNull('date_to')
            ->orderBy('id')
            ->when($service, function($query) use($service) {
                $query->where('service_type', '=', 'service')
                    ->where('service_id', '=', $service);
            })
            ->when($analysis, function($query) use($analysis) {
                $query->where('service_type', '=', 'analysis')
                    ->where('service_id', '=', $analysis);
            })
            ->chunkById(100, function($prices) {
                $prices->loadMissing('clinics');
                foreach ($prices as $price) {
                    $this->tryClose($price);
                }
                $this->info("Next chunk [{$this->chunkNumber}]...");
                $this->chunkNumber++;
            });

        $this->info('Done!');
    }

    /**
     * @param Price $price
     */
    protected function tryClose($price)
    {
        $clinicIds = $price->clinics->pluck('id')->all();
        $now = Carbon::now()->format('Y-m-d');
        $base = Price::where('service_id', $price->service_id)
            ->where('service_type', $price->service_type)
            ->where('set_id', self::BASE_SET_ID)
            ->where('date_from', '<=', $now)
            ->where(function($query) use($now) {
                $query->whereNull('date_to')
                    ->orWhere('date_to', '>', $now);
            })
            ->join('price_clinics', function($join) use ($clinicIds) {
                $join->on('price_clinics.price_id', '=', 'prices.id')
                    ->whereIn('price_clinics.clinic_id', $clinicIds);
            })
            ->with('clinics')
            ->get();

        if ($base->count() !== 0) {
            $baseClinics = [];
            foreach ($base as $bc) {
                foreach ($bc->clinics as $bcc) {
                    $baseClinics[] = $bcc->id;
                }
            }
            $closeClinicsId = array_filter($clinicIds, function($id) use($baseClinics) {
                return !in_array($id, $baseClinics);
            });
            if (count($closeClinicsId) !== 0) {
                $message = sprintf(
                    '%s #%d has insurance price [%d] from %s, clinicId = %d, setID = %d, close?',
                    $price->service_type, $price->service_id, $price->id, $price->date_from,
                    implode(',', $closeClinicsId), $price->set_id
                );

                if ($this->interactive && !$this->confirm($message)) {
                    return;
                }

                $keepId = array_filter($clinicIds, function($id) use($baseClinics) {
                    return in_array($id, $baseClinics);
                });

                $price->clinics = $keepId;
                $price->save();

                $closed = new Price();
                $closed->service_id = $price->service_id;
                $closed->service_type = $price->service_type;
                $closed->date_from = $price->date_from;
                $closed->date_to = Carbon::now()->subDays(1)->format('Y-m-d');
                $closed->cost = $price->cost;
                $closed->self_cost = $price->self_cost;
                $closed->currency = $price->currency;
                $closed->set_id = $price->set_id;
                $closed->clinics = $closeClinicsId;
                $closed->save();

                if ($this->interactive) {
                    $this->info('Price has been closed.');
                } else {
                    $this->info($message . ' Closed.');
                }
            }
            return;
        }

        $message = sprintf(
            '%s #%d has insurance price [%d] from %s, clinicId = %d, setID = %d, close?',
            $price->service_type, $price->service_id, $price->id, $price->date_from,
            implode(',', $clinicIds), $price->set_id
        );

        if ($this->interactive && !$this->confirm($message)) {
            return;
        }

        $price->date_to = Carbon::now()->subDays(1)->format('Y-m-d');
        $price->save();

        if ($this->interactive) {
            $this->info('Price has been closed.');
        } else {
            $this->info($message . ' Closed.');
        }
    }
}
