<?php

namespace App\V1\Traits\Controllers;

use App\V1\Repositories\Query\PriceFilter;
use Illuminate\Support\Arr;

trait PriceList 
{
    /**
     * Load prices for services
     *
     * @param \Illuminate\Support\Collection $collection
     * @param mixed $filters
     */
    public function loadPrices($collection, $filters)
    {   
      
        $values = $filters->getFilterValues();
        $priceFilters = app()->makeWith(PriceFilter::class, ['request' => null]);
        $priceFilters->setFilter(array_filter([
            'clinic' => Arr::get($values, 'has_price.clinic', Arr::get($values, 'clinic')),
            'set_type' => Arr::get($values, 'has_price.set', Arr::get($values, 'set_type')),
            'active_from' => Arr::get($values, 'has_price.from'),
            'active_to' => Arr::get($values, 'has_price.to'),
            'recent' => (Arr::get($values, 'has_price.from') || Arr::get($values, 'has_price.to') || Arr::get($values, 'price_start_at.from')) ? null : 1,
            'price_start_at' => Arr::get($values, 'price_start_at'),
            
        ]));
       
        $collection->load(['prices' => function($query) use($priceFilters) {
            $priceFilters->apply($query);
            $query->orderBy('date_from', 'desc');
        }]);
    }
}