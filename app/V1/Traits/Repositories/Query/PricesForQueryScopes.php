<?php

namespace App\V1\Traits\Repositories\Query;

use Illuminate\Support\Arr;
use App\V1\Models\InsuranceCompany;

trait PricesForQueryScopes
{
    /**
     * TODO: implement parametrized scopes, such as
     * 
     * ['scope1', 'scope2': ['param1', 'param2']]
     * 
     * @inherit
     */ 
    public function scopePricesForQuery($object)
    {
        $date = null;
        $clinic = null;
        $setType = [];
        $request = $this->request;
        $insurer = $request->input('withInsurer', null);

        foreach (Arr::dot($request->input('filters', [])) as $key => $value) {
            if (strpos($key, 'hasPrice.from') !== false) {
                $date = $value;    
            } elseif (strpos($key, 'hasPrice.clinic') !== false) {
                $clinic = $value;    
            } elseif (strpos($key, 'hasPrice.set') !== false) {
                $setType[] = $value;    
            }
        }

        $object->load(['prices' => function($query) use ($date, $clinic, $setType, $insurer) {
            $query->where(function($query) use ($date) {
                $query->where(function($query) use($date) {
                    $query->whereNull('date_to')
                        ->orWhere('date_to', '>=', $date);
                })
                ->where('date_from', '<=', $date);
            })->join('price_clinics', function($join) use ($clinic) {
                $join->on('price_clinics.price_id', '=', 'prices.id')
                    ->where('price_clinics.clinic_id', '=', $clinic);
            })
            ->whereIn($query->qualifyColumn('set_id'), function($query) use ($setType, $insurer) {
                $query->select('price_sets.id')
                    ->from('price_sets')
                    ->whereIn('type', $setType);
                if ($insurer) {
                    $query->orWhere(function($query) use($insurer) {
                        $query->where('owner_type', '=', InsuranceCompany::RELATION_TYPE)
                            ->where('owner_id', '=', $insurer);
                    });
                }
            });
            $query->with('price_set');
        }]);
    }
}