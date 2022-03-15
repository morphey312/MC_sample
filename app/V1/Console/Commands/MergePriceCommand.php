<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\V1\Models\Price;
use Carbon\Carbon;

class MergePriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:merge {--interactive} {--clinic=} {--service=} {--analysis=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicated prices';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $interactive = $this->option('interactive');
        $clinic = $this->option('clinic');
        $service = $this->option('service');
        $analysis = $this->option('analysis');
        
        $duplicated = DB::table('prices')
            ->selectRaw("group_concat(distinct prices.id order by prices.id separator ',') as ids")
            ->join('price_clinics', function($join) use($clinic, $service, $analysis) {
                $join->on('price_clinics.price_id', '=', 'prices.id');
                if ($clinic) {
                    $join->where('price_clinics.clinic_id', '=', $clinic);
                }
                if ($service) {
                    $join->where('prices.service_id', '=', $service)
                        ->where('prices.service_type', '=', 'service');
                }
                if ($analysis) {
                    $join->where('prices.service_id', '=', $analysis)
                        ->where('prices.service_type', '=', 'analysis');
                }
            })
            ->groupBy(
                'prices.date_from', 
                'prices.set_id', 
                'prices.service_id', 
                'prices.service_type', 
                'price_clinics.clinic_id'
            )
            ->havingRaw('count(distinct prices.id) > 1')
            ->get();
        
        $this->info(sprintf('%d duplicated prices have been found', $duplicated->count()));

        $caseNumber = 1;
        foreach ($duplicated as $ids) {
            $prices = Price::with('clinics')->find(explode(',', $ids->ids));
            $this->merge($prices, $interactive, $caseNumber++);
        }

        $this->info('Done!');
    }

    /**
     * Merge set of prices
     * 
     * @param array $prices
     * @param bool $interactive
     * @param int $caseNumber
     */
    protected function merge($prices, $interactive, $caseNumber)
    {
        $main = null;
        foreach ($prices as $price) {
            if ($main === null) {
                $main = $price;
                continue;
            }
            if ($main->clinics->count() > $price->clinics->count()) {
                $main = $price;
                continue;
            } elseif ($main->clinics->count() === $price->clinics->count()) {
                if ($main->date_to === null && $price->date_to !== null) {
                    $main = $price;
                    continue;
                }
                if ($main->date_to !== null && 
                    $price->date_to !== null && 
                    $main->date_to->isBefore($price->date_to)) 
                {
                    $main = $price;
                    continue;
                }
            }
        }
        
        $secondary = $prices->filter(function($price) use($main) {
            return $price->id !== $main->id
                && $price->clinics->pluck('id')->intersect(
                    $main->clinics->pluck('id')
                )->count() !== 0;
        });

        if ($secondary->count() == 0) {
            if ($interactive) {
                $this->info(sprintf('Case #%d was self-resolved...', $caseNumber));
            }
            return;
        }

        $mainClinicIds = $main->clinics->pluck('id');
        $toRemove = [];
        $toDisjoin = [];
        $num = 1;
        foreach ($secondary as $price) {
            $num++;
            $secondaryClinicIds = $price->clinics->pluck('id')->diff($mainClinicIds);
            if ($secondaryClinicIds->count() === 0) {
                $toRemove[] = [
                    sprintf('#%d', $num),
                    $price,
                ];
            } else {
                $setDate = null;
                if ($main->date_to !== null) {
                    if ($price->date_to !== null) {
                        if ($main->date_to->isAfter($price->date_to)) {
                            $setDate = $main->date_to->format('Y-m-d');
                        }
                    } else {
                        $setDate = Carbon::now()->format('Y-m-d');
                    }
                }
                $toDisjoin[] = [
                    sprintf('#%d', $num),
                    $price,
                    $secondaryClinicIds,
                    $setDate,
                ];
            }
        }

        if ($interactive) {
            $this->info(sprintf('Case #%d:', $caseNumber));
            $this->displayPrice($main, 1);
            $num = 2;
            foreach ($secondary as $price) {
                $this->displayPrice($price, $num++);
            }
            $this->info('Solution:');
            foreach ($toRemove as $stuff) {
                list($num, $price) = $stuff;
                $this->info(sprintf(
                    '* Move stuff from price %s to price #1 and delete price %s;',
                    $num, 
                    $num
                ));
            }
            foreach ($toDisjoin as $stuff) {
                list($num, $price, $clinicIds, $setDate) = $stuff;
                $this->info(sprintf(
                    '* For price %s set clinics = [%s]%s;',
                    $num, 
                    implode(', ', $clinicIds->all()),
                    ($setDate === null ? '' : sprintf(
                        ', end date = %s',
                        $setDate
                    ))
                ));
            }
            if (!$this->confirm('Accept this solution?')) {
                $this->info('Skipping this case...');
                return;
            }
        }

        foreach ($toRemove as $stuff) {
            list($num, $price) = $stuff;
            $main->reparent($price, [
                'analysis_results',
                'appointment_services',
                'assigned_services',
            ]);
            $price->delete();
        }

        foreach ($toDisjoin as $stuff) {
            list($num, $price, $clinicIds, $setDate) = $stuff;
            $price->clinics()->sync($clinicIds->all());
            if ($setDate !== null) {
                $price->where('id', '=', $price->id)->update([
                    'date_to' => $setDate,
                ]);
            }
        }

        if ($interactive) {
            $this->info('Solved!');
        }
    }

    /**
     * Display price info
     * 
     * @param Price $price
     * @param int $number
     */
    protected function displayPrice($price, $number) 
    {
        $this->info(sprintf(
            '%d) %s - %s, %s = %d, cost = %d, clinics = [%s], set = %d, ID = %d', 
            $number,
            $price->date_from->format('Y-m-d'),
            $price->date_to ? $price->date_to->format('Y-m-d') : '...',
            $price->service_type,
            $price->service_id,
            $price->cost,
            implode(',', $price->clinics->pluck('id')->all()),
            $price->set_id,
            $price->id
        ));
    }
}
