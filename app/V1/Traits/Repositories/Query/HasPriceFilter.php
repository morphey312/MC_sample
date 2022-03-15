<?php

namespace App\V1\Traits\Repositories\Query;

use App\V1\Models\Price;
use Carbon\Carbon;

trait HasPriceFilter
{
    /** 
     * Filter by price existing
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterHasPrice($query, $value)
    {
        if (is_array($value)) {
            $query->whereHas('prices', function($query) use($value) {
                return $this->priceSubQuery($query, $value);
            });
        } else {
            $query->has('prices');
        }
    }

    /** 
     * Filter by price not existing
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterHasNoPrice($query, $value)
    {
        if (is_array($value)) {
            $query->whereDoesntHave('prices', function($query) use($value) {
                return $this->priceSubQuery($query, $value);
            }); 
        } else {
            $query->whereDoesntHave('prices');
        }
    }
    
    /** 
     * Create filter subquery for price date_from and/or date_to
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    protected function priceSubQuery($query, $value)
    {
        if (!empty($value['from'])) {
            $query->where(function($query) use ($value) {
                $this->priceDateFromSubquery($query, $this->safeString($value['from']));
            });
        }

        if (!empty($value['to'])) {
            $query->where($query->qualifyColumn('date_from'), '<=', $this->safeString($value['to']));
        }

        if (!empty($value['clinic'])) {
            $query->join('price_clinics', function($join) use($value) {
                $join->on('prices.id', '=', 'price_clinics.price_id')
                    ->whereIn('price_clinics.clinic_id', $this->safeArray($value['clinic']));
            });
        }
        
        if (!empty($value['set'])) {
            $query->whereIn($query->qualifyColumn('set_id'), function($query) use($value) {
                $query->select('price_sets.id')
                    ->from('price_sets')
                    ->whereIn('type', $this->safeArray($value['set']));
            });
        }

        if (!empty($value['set_id'])) {
            $query->whereIn($query->qualifyColumn('set_id'), $this->safeArray($value['set_id']));
        }

        return $query;
    }

    /** 
     * Create filter subquery for price date_from
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $from
     */
    protected function priceDateFromSubquery($query, $from)
    {
        return  $query->where(function($query) use ($from) {
            $query->where($query->qualifyColumn('date_from'), '<=', $from)
                ->where(function($query) use ($from) {
                    $query->whereNull($query->qualifyColumn('date_to'))    
                        ->orWhere($query->qualifyColumn('date_to'), '>=', $from);
                });
        })->orWhere($query->qualifyColumn('date_from'), '>=', $from);
    }

    /** 
     * Filter by price changed at
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     */
    public function filterPriceStartAt($query, $value)
    {   
        if (is_array($value)) {
            $startDateTime = Carbon::parse($value['from'])->startOfDay();
            $endDateTime = Carbon::parse($value['to'])->endOfDay();
            $query->whereHas('prices', function($query) use($startDateTime, $endDateTime) {
                $query->whereBetween($query->qualifyColumn('date_from'), [$startDateTime, $endDateTime]);
                $query->join('price_sets','price_sets.id', '=', 'prices.set_id')->where('type', '=', Price::BASE_PRICE);
            });
    
        } else {
            $query->has('prices');
        }
    }
}