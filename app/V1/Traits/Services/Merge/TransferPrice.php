<?php

namespace App\V1\Traits\Services\Merge;

trait TransferPrice
{
    /**
     * Transfer price from one service to another
     * 
     * @param \App\V1\Models\Price $srcPrice
     * @param mixed $service
     */ 
    protected function transferPrice($srcPrice, $service)
    {
        foreach ($service->prices as $dstPrice) {
            if (!$this->pricesHaveClinicsIntersection($srcPrice, $dstPrice)) {
                continue;
            }
            
            if ($srcPrice->date_from == $dstPrice->date_from) {
                // The given price has conflict with service price
                $uniqueClinics = $this->withoutClinics($srcPrice, $dstPrice->clinics->modelKeys());
                if (count($uniqueClinics) === 0) {
                    // The given price has the same clinics, so we will just replace it with service price
                    $this->replacePrice($srcPrice, $dstPrice);
                    return;
                }
                $srcPrice->clinics = $uniqueClinics;
            }
        }
        
        // Save price on new service, all history will be lined up during save
        $srcPrice->service = $service;
        if ($srcPrice->save()) {
            $service->prices->add($srcPrice);
        }
    }
    
    /**
     * Replace links from one price to another
     * 
     * @param \App\V1\Models\Price $source
     * @param \App\V1\Models\Price $destination
     */ 
    protected function replacePrice($source, $destination)
    {
        $destination->reparent($source, [
            'analysis_results',
            'appointment_services',
            'assigned_services',
        ], false);
        
        $this->mergePriceClinics($source, $destination);
        
        $source->delete();
    }
    
    /**
     * Check if these prices share some clinics
     * 
     * @param \App\V1\Models\Price $price1
     * @param \App\V1\Models\Price $price2
     * 
     * @return bool
     */ 
    protected function pricesHaveClinicsIntersection($price1, $price2)
    {
        return array_intersect(
            $price1->clinics->modelKeys(),
            $price2->clinics->modelKeys()
        ) !== [];
    }
    
    /**
     * Get price clinics without specified list
     * 
     * @param \App\V1\Models\Price $price
     * @param array $clinicIds
     * 
     * @return array
     */ 
    protected function withoutClinics($price, $clinicIds)
    {
        $result = [];
        foreach ($price->clinics as $clinic) {
            if (!in_array($clinic->id, $clinicIds)) {
                $result[] = $clinic;
            }
        }
        return $result;
    }
    
    /**
     * Merge clinics of two prices
     * 
     * @param \App\V1\Models\Price $source
     * @param \App\V1\Models\Price $destination
     */
    protected function mergePriceClinics($source, $destination)
    {
        $destClinics = $destination->clinics;
        foreach ($source->clinics as $clinic) {
            if ($destClinics->where('id', $clinic->id)->first() === null) {
                $destClinics->add($clinic);
                $destination->clinics()->attach($clinic);
            }
        }
    }
}