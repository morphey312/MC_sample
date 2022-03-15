<?php

namespace App\V1\Traits\Models;

use App\V1\Models\Price;
use App\V1\Models\InsuranceCompany;
use Carbon\Carbon;

trait HasPrice
{
    /**
     * Related prices
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */ 
    public function prices()
    {
        return $this->morphMany(Price::class, 'service')->with('clinics');
    }
    
    /**
     * Related actual prices (active or scheduled)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */ 
    public function actual_prices()
    {
        return $this->prices()
            ->where(function($query) {
                $query->whereNull('date_to')
                    ->orWhere('date_to', '>=', Carbon::now()->endOfDay());
            });
    }
    
    /**
     * Related active prices
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */ 
    public function active_prices()
    {
        return $this->prices()
            ->where('date_from', '<=', Carbon::now()->startOfDay())
            ->where(function($query) {
                $query->whereNull('date_to')
                    ->orWhere('date_to', '>=', Carbon::now()->toDateString());
            });
    }

    /**
     * Related active base prices
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */ 
    public function active_base_prices()
    {
        return $this->active_prices()
            ->whereIn('set_id', function($query) {
                $query->select('price_sets.id')
                    ->from('price_sets')
                    ->where('price_sets.type', '=', Price::BASE_PRICE);
            });
    }

    /**
     * Get model related prices in clinic by date and price type
     * 
     * @return collection
     */ 
    public function actualPriceOnDate($date, $clinic, $setType)
    {
        if (!is_array($setType)) {
            $setType = [$setType];
        }

        return $this->prices()
            ->with('price_set')
            ->where(function($query) use ($date) {
                $query->where(function($query) use($date) {
                    $query->whereNull('date_to')
                        ->orWhere('date_to', '>=', $date);
                })
                ->where('date_from', '<=', $date);
            })->join('price_clinics', function($join) use ($clinic) {
                $join->on('price_clinics.price_id', '=', 'prices.id')
                    ->where('price_clinics.clinic_id', '=', $clinic);
            })
            ->whereIn('set_id', function($query) use ($setType) {
                $query->select('price_sets.id')
                    ->from('price_sets')
                    ->whereIn('type', $setType);
            })
            ->get();
    }
}